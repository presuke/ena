import serial
import json
import time
import datetime
import requests
import minimalmodbus
import math
import sqlite3


READ_FUNCTION_CODE = 3
WRITE_FUNCTION_CODE = 6

MAX_UTILITY_CHARGE_CURRENT = 80
MIN_UTILITY_CHARGE_CURRENT = 0
MULTIPLIER_UTILITY_CHARGE_CURRENT = 5

MAX_CHARGE_CURRENT = 80
MIN_CHARGE_CURRENT = 0
MULTIPLIER_CHARGE_CURRENT = 5

INVERTER_COMMANDS_READ = {
    #region Reading Commands
    'battery_voltage': (0x0101, 1, READ_FUNCTION_CODE, False),
    'battery_current': (0x0102, 1, READ_FUNCTION_CODE, True),
    'battery_charge_power':(0x010e, 0, READ_FUNCTION_CODE, False),
    'battery_soc': (0x0100, 0, READ_FUNCTION_CODE, False),
    'battery_max_charge_current': (0xe20a, 1, READ_FUNCTION_CODE, False),
    'pv_voltage': (0x0107, 1, READ_FUNCTION_CODE, False),
    'pv_current': (0x0108, 1, READ_FUNCTION_CODE, False),
    'pv_power': (0x0109, 0, READ_FUNCTION_CODE, False),
    'pv_battery_charge_current': (0x0224, 1, READ_FUNCTION_CODE, False),
    'grid_voltage': (0x0213, 1, READ_FUNCTION_CODE, False),  
    'grid_input_current': (0x0214, 1, READ_FUNCTION_CODE, False),
    'grid_battery_charge_current': (0x021E, 1, READ_FUNCTION_CODE, False),
    'grid_frequency':(0x0215, 2, READ_FUNCTION_CODE, False),
    'grid_battery_charge_max_current': (0xe205, 1, READ_FUNCTION_CODE, False),
    'inverter_voltage': (0x0216, 1, READ_FUNCTION_CODE, False),
    'inverter_current': (0x0219, 1, READ_FUNCTION_CODE, False),
    'inverter_frequency': (0x0218, 2, READ_FUNCTION_CODE, False),
    'inverter_power': (0x021b, 0, READ_FUNCTION_CODE, False),
    'inverter_output_priority': (0xe204, 0, READ_FUNCTION_CODE, False),
    'inverter_charger_priority': (0xe20f, 0, READ_FUNCTION_CODE, False), 
    'temp_dc': (0x0221, 1, READ_FUNCTION_CODE, True),
    'temp_ac': (0x0222, 1, READ_FUNCTION_CODE, True),
    'temp_tr': (0x0223, 1, READ_FUNCTION_CODE, True),
    #endregion
}

INVERTER_COMMANDS_WRITE = {
    #region Writing Commands
    'grid_battery_charge_max_current_write': (0xe205, 1, WRITE_FUNCTION_CODE, False),
    'battery_max_charge_current_write': (0xe20a, 1, WRITE_FUNCTION_CODE, False),
    'inverter_output_priority_write': (0xe204, 0, WRITE_FUNCTION_CODE, False),
    'inverter_charger_priority_write': (0xe20f, 0, WRITE_FUNCTION_CODE, False),
    #endregion
    #register: int, value: [int, float], decimals: int = 0, functioncode: int = 6, signed: bool = False

}

INVERTER_COMMANDS_WRITE_VALUE = {
    'output_priority_SOL': 0,
    'output_priority_UTI': 1,
    'output_priority_SBU': 2,
    'chargerPriority_CSO': 0,
    'chargerPriority_CUB': 1,
    'chargerPriority_SNU': 2,
    'chargerPriority_OSO': 3,
}

DB = {
    'table':{
        'data': {
            'name':'hidata',
            'fields': {
                'create_at':'INTEGER PRIMARY KEY',
                'battery_voltage':'REAL',
                'battery_current':'REAL',
                'battery_charge_power':'REAL',
                'battery_soc':'REAL',
                'battery_max_charge_current':'REAL',
                'pv_voltage':'REAL',
                'pv_current':'REAL',
                'pv_power':'REAL',
                'pv_battery_charge_current':'REAL',
                'grid_voltage':'REAL',
                'grid_input_current':'REAL',
                'grid_battery_charge_current':'REAL',
                'grid_frequency':'REAL',
                'grid_battery_charge_max_current':'REAL',
                'inverter_voltage':'REAL',
                'inverter_current':'REAL',
                'inverter_frequency':'REAL',
                'inverter_power':'REAL',
                'inverter_output_priority':'REAL',
                'inverter_charger_priority':'REAL',
                'temp_dc':'REAL',
                'temp_ac':'REAL',
                'temp_tr':'REAL',
            }
        },
        'error':{
            'name':'error',
            'fields': {
                'create_at':'INTEGER PRIMARY KEY',
                'battery_voltage':'REAL'
            }
        }
    }
}

GRID_AREA = ['01','02','03','04','05','06','07','08','09','10']

#SEC
ROCCESS_TIMING = {
    'margin': 10,
    'get_hibridinverter_parameter': 60,
    'server_regist': 120,
    'report_server': 300
}

def loadSetting():
    ret = {}
    try:
        stream_reader = open('setting.json', 'r', encoding='UTF-8')
        ret = json.load(stream_reader)
    except Exception as e:
        print("error:", e)
    return ret

def getComInstance(setting_json):
    instr = {}
    try:
        instr = minimalmodbus.Instrument(setting_json['com']['deviceid'], setting_json['com']['slaveaddress'])
        instr.serial.baudrate = setting_json['com']['baudrate']
        instr.serial.timeout = setting_json['com']['timeout']
        instr.debug = setting_json['com']['debug']
    except Exception as e:
        print("error:", e)
    return instr    

def openDBConnection():
    ret = {}
    try:
        sqlite_file = setting_json['db']['file']
        ret = sqlite3.connect(sqlite_file)
    except Exception as e:
        print("error:", e)
    return ret

def createTable():
    ret = {}
    try:
        table = DB.get('table').get('data')
        fields = []
        for field in table.get('fields').keys():
            fields.append(field + ' ' + table.get('fields').get(field))
        sql = 'CREATE TABLE IF NOT EXISTS ' + str(table.get('name')) + ' (' + ','.join(fields) + ')'
        ret = executeSql(sql)
    except Exception as e:
        print("error:", e)
    return ret

def executeSql(sql):
    ret = False
    con = openDBConnection()
    try:
        cur = con.cursor()
        ret = cur.execute(sql)
        con.commit()
    except Exception as e:
        print("error:", e)
        con.rollbakck()
    finally:
        cur.close()
        con.close()
    return ret
        
def saveValues(values):
    ret = {}
    try:
        table = DB.get('table').get('data')
        fields = []
        datas = []
        for field in table.get('fields').keys():
            fields.append(field)
            datas.append(str(values[field]))
        
        sql = 'INSERT INTO ' + str(table.get('name')) + ' (' + ','.join(fields) + ')VALUES(' +  ','.join(datas) + ')'
        ret = executeSql(sql)
    except Exception as e:
        print("error:", e)
    return ret

def readValues(timestamp):
    ret = []
    try:

        table = DB.get('table').get('data')
        fields = []
        for field in table.get('fields').keys():
            fields.append(field)
        sql = 'SELECT ' + ','.join(fields) + ' FROM ' + str(table.get('name')) + ' WHERE create_at >=' + str(timestamp) + ' ORDER BY create_at ASC'

        con = openDBConnection()
        cur = con.cursor()
        rec = cur.execute(sql)
    
        for row in rec:
            idx = 0
            dat = {}
            for clm in rec.description:
                key = clm[0]
                val = row[idx]
                dat[key] = val
                idx += 1
                if(key == 'create_at'):
                    a = datetime.datetime.fromtimestamp(val)
                    print("data:" + str(a))
                
            ret.append(dat)
    except Exception as e:
        print("error:", e)
    finally:
        cur.close()
        con.close()
    return ret

def deleteValues(timestamp):
    ret = {}
    try:
        table = DB.get('table').get('data')
        sql = 'DELETE FROM ' + str(table.get('name')) + ' WHERE create_at <' + str(timestamp)
        ret = executeSql(sql)
    except Exception as e:
        print("error:", e)
    return ret

def write_register(instr, value: [int, float], register: int, decimals: int = 0, functioncode: int = 6, signed: bool = False):
    ret = {}
    try:
        ret = instr.write_register(register, value, decimals, functioncode, signed)
    except Exception as e:
        print("error:", e)
    return ret

def main():
    global setting_json
    setting_json = loadSetting()
    instr = getComInstance(setting_json)
    createTable()

    servertime_gap = 0
    url = setting_json['api']['host'] + '/api/v1/log/serverTime'
    res = requests.get(url, json = {})
    res_json = json.loads(res.text)
    if(res_json['code'] == 0):
        servertime = res_json['time']
        ut = math.floor(time.time())
        servertime_gap = servertime - ut

    area = 0
    cnt = 0
    while True:
        try:
            ut = math.floor(time.time()) + servertime_gap
            #get inverter values
            if ut % ROCCESS_TIMING.get('get_hibridinverter_parameter') <= ROCCESS_TIMING.get('margin'):
                cnt += 1
                print('##################' + str(cnt) + 'th try##################')
                values = {}
                values['create_at'] = ut
                #instr.read_register(register, decimals, functioncode, signed)
                for key in INVERTER_COMMANDS_READ.keys():
                    val = instr.read_register(*INVERTER_COMMANDS_READ.get(key))
                    values[key] = val
                    time.sleep(0.1)

                saveValues(values)

                #get servers regist
                if(ut % ROCCESS_TIMING.get('server_regist') <= ROCCESS_TIMING.get('margin')):
                    try:
                        url = setting_json['api']['host'] + '/api/v1/regist/read'
                        post_data = {'user':setting_json['user']}
                        res = requests.post(url, json = post_data)
                        res_json = json.loads(res.text)
                        if(res_json['code'] == 0):
                            if(res_json['regist'] != None):
                                regist = res_json['regist']['regist']
                                value = res_json['regist']['value']
                                
                                match regist:
                                    case 'inverter_output_priority_write':
                                        key = 'inverter_output_priority'
                                        val = INVERTER_COMMANDS_WRITE_VALUE.get(value)
                                        
                                write_register(instr, val, *INVERTER_COMMANDS_WRITE.get(regist))
                                result = instr.read_register(*INVERTER_COMMANDS_READ.get(key))
                                
                                url = setting_json['api']['host_debug'] + '/api/v1/regist/report'
                                post_data = {'regist':res_json['regist'], 'request': val, 'result': result, 'done_at': ut}
                                requests.post(url, json = post_data)
                    except Exception as e:
                        print("error:", e)
                        
                #report_server inverter values
                if(ut % ROCCESS_TIMING.get('report_server') <= ROCCESS_TIMING.get('margin')):
                    data = readValues(ut - 3600)
                    url = setting_json['api']['host'] + '/api/v1/log/write'
                    post_data = {'user':setting_json['user'], 'datas': data}
                    res = requests.post(url, json = post_data)
                    res_json = json.loads(res.text)
                    if(res_json['code'] == 0):
                        print('request-success')
                        deleteValues(ut - (6 * 3600))
                    else:
                        print('error:' + res.text) 
                    area += 1
                    area %= len(GRID_AREA)
                    url ='https://looop-denki.com/api/prices?select_area=' + GRID_AREA[area]
                    res = requests.get(url)
                    contents = res.content
                    url = setting_json['api']['host'] + '/api/v1/regist/recordGridPrice'
                    post_data = {'contents':contents, 'time':ut, 'area':GRID_AREA[area]}
                    requests.post(url, json = post_data)
                    
                time.sleep(ROCCESS_TIMING.get('margin'))
            else:
                print(ut % 300)
                time.sleep(1)
                
        except Exception as e:
            print("error:", e)
            time.sleep(ROCCESS_TIMING.get('margin'))

#run main
main()
