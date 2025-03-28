import serial
import json
import time
import datetime
import requests
import minimalmodbus
import math
import pygame
import sqlite3
import logging
import subprocess
from decimal import *
from logging.handlers import TimedRotatingFileHandler

pygame.mixer.init(frequency=44100)
pygame.mixer.music.load('sheep02Passage.mp3')
pygame.mixer.music.play(100)

logger = logging.getLogger('hi')
logger.setLevel(logging.DEBUG)

handler = TimedRotatingFileHandler('log.log', when='midnight', interval=1, backupCount=7, atTime=datetime.time(0,0,0))
handler.setLevel(logging.DEBUG)
handler_formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')

handler.setFormatter(handler_formatter)
logger.addHandler(handler)

logger.info("start ena.py")


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
    'grid_battery_charge_max_current': (0xe205, 1, WRITE_FUNCTION_CODE, False),
    'battery_max_charge_current': (0xe20a, 1, WRITE_FUNCTION_CODE, False),
    'inverter_output_priority': (0xe204, 0, WRITE_FUNCTION_CODE, False),
    'inverter_charger_priority': (0xe20f, 0, WRITE_FUNCTION_CODE, False),
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
PROCCESS_TIMING = {
    'interval': 5,
    'get_hibridinverter_parameter': 30,
    'server_regist': 60,
    'report_server': 60,
    'report_gridprice': 1800,
}

def loadSetting():
    ret = {}
    try:
        stream_reader = open('setting.json', 'r', encoding='UTF-8')
        ret = json.load(stream_reader)
    except Exception as e:
        logger.exception(e)
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
        logger.exception(e)
        print("error:", e)
    return instr    

def openDBConnection():
    ret = {}
    try:
        sqlite_file = setting_json['db']['file']
        ret = sqlite3.connect(sqlite_file)
    except Exception as e:
        logger.exception(e)
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
        logger.exception(e)
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
        logger.exception(e)
        print("error:", e)
        con.rollback()
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
        logger.exception(e)
        print("error:", e)
    return ret

def readValues(dtFrom, dtTo):
    ret = []
    try:

        table = DB.get('table').get('data')
        fields = []
        for field in table.get('fields').keys():
            fields.append(field)
        sql = 'SELECT ' + ','.join(fields) + ' FROM ' + str(table.get('name')) + ' WHERE create_at >=' + str(dtFrom) + ' AND create_at <=' + str(dtTo) + ' ORDER BY create_at ASC'

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
                
            ret.append(dat)
    except Exception as e:
        logger.exception(e)
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
        log.exception(e)
        print("error:", e)
    return ret

def write_register(instr, value: [int, float], register: int, decimals: int = 0, functioncode: int = 6, signed: bool = False):
    ret = {}
    try:
        ret = instr.write_register(register, value, decimals, functioncode, signed)
    except Exception as e:
        log.exception(e)
        print("error:", e)
    return ret

def main():
    try:
        logger.info("get setting st")
        global setting_json
        setting_json = loadSetting()
        logger.info("get ComInstance st")
        instr = getComInstance(setting_json)
        logger.info("create table st")
        createTable()

        servertime_gap = 0
        logger.info("get server time")
        url = setting_json['api']['host'] + '/api/v1/log/serverTime'
        res = requests.get(url, json = {})
        res_json = json.loads(res.text)
        logger.info("server time:" + res.text)
        if(res_json['code'] == 0):
            servertime = res_json['time']
            ut = math.floor(time.time())
            servertime_gap = servertime - ut
            logger.info("ServerTime:" + str(servertime))
            logger.info("ClientTime:" + str(ut))
            logger.info("Gap:" + str(servertime_gap))

    except Exception as e:
        logger.info("reboot main exception")
        log.exception(e)
        reboot()
        
    area = 0
    cnt = 0
    while True:
        try:
            ut = math.floor(time.time()) + servertime_gap

            # UTCのdatetimeオブジェクトに変換
            utc_time = datetime.datetime.fromtimestamp(ut, tz=datetime.timezone.utc)
            # 日本時間のタイムゾーンオブジェクトを作成
            jst_timezone = datetime.timezone(datetime.timedelta(hours=9))
            # タイムゾーンを日本時間に設定
            jst_time = utc_time.astimezone(jst_timezone)
            # フォーマットされた文字列に変換
            formatted_time = jst_time.strftime('%Y-%m-%d %H:%M:%S')

            #reboot
            if(setting_json['reboot'] in formatted_time):
                logger.info("reboot schedule:" + setting_json['reboot'] + " in " + formatted_time)
                reboot()
            
            #get inverter values
            if ut % PROCCESS_TIMING.get('get_hibridinverter_parameter') <= PROCCESS_TIMING.get('interval'):
                cnt += 1
                print('##################' + str(cnt) + 'th try##################')
                logger.info("No." + str(cnt))
                values = {}
                values['create_at'] = ut
                #instr.read_register(register, decimals, functioncode, signed)
                for key in INVERTER_COMMANDS_READ.keys():
                    val = instr.read_register(*INVERTER_COMMANDS_READ.get(key))
                    values[key] = val
                    time.sleep(0.1)

                saveValues(values)

                #get servers regist
                if(ut % PROCCESS_TIMING.get('server_regist') <= PROCCESS_TIMING.get('interval')):
                    try:
                        url = setting_json['api']['host'] + '/api/v1/regist/readRegistSetting'
                        post_data = {'report':0, 'user':setting_json['user']}
                        res = requests.post(url, json = post_data)
                        res_json = json.loads(res.text)
                        logger.info(res.text)
                        if res_json['code'] == 0:
                            if res_json['regists'] != None:
                                for item in res_json['regists']:
                                    mode = int(item['mode'])
                                    regist = json.loads(item['regist'])
                                    result = []
                                    if mode == 0:
                                        if item['result'] == '':
                                            for key in regist.keys():
                                                val = int(regist[key])
                                                key2 = key.replace("_write","")
                                                write_register(instr, val, *INVERTER_COMMANDS_WRITE.get(key2))
                                                time.sleep(0.1)
                                                result.append({key2 : instr.read_register(*INVERTER_COMMANDS_READ.get(key2))})
                                                time.sleep(0.1)
                                    elif mode == 1:
                                        dt = datetime.datetime.fromisoformat(res_json['now'])
                                        h = dt.hour
                                        hSt = int(regist['midnightSt'])
                                        hEd = int(regist['midnightEd'])
                                        dVoltageSt = Decimal(regist['voltageGridingSt'])
                                        dVoltageEd = Decimal(regist['voltageGridingEd'])
                                        
                                        voltage = instr.read_register(*INVERTER_COMMANDS_READ.get('battery_voltage'))
                                        time.sleep(0.1)
                                        output = instr.read_register(*INVERTER_COMMANDS_READ.get('inverter_output_priority'))
                                        time.sleep(0.1)
                                        
                                        #power output source
                                        key = 'inverter_output_priority'
                                        if((h >= hSt and h < 24) or (h >= 0 and h < hEd)):
                                            if(output != 1 and (voltage < dVoltageSt or regist['forceSt'])):
                                                logger.info('auto grid on:{voltage:' + str(voltage) + ',output:' + str(output) + '}')
                                                write_register(instr, 1, *INVERTER_COMMANDS_WRITE.get(key))
                                                time.sleep(0.1)
                                                result.append({key : instr.read_register(*INVERTER_COMMANDS_READ.get(key))})
                                                time.sleep(0.1)                                                
                                            elif(output == 1 and voltage >= dVoltageEd):
                                                write_register(instr, 2, *INVERTER_COMMANDS_WRITE.get(key))
                                                time.sleep(0.1)
                                                result.append({key : instr.read_register(*INVERTER_COMMANDS_READ.get(key))})
                                                time.sleep(0.1)
                                                logger.info('auto grid off(voltage):{voltage:' + str(voltage) + ',output:' + str(output) + '}')
                                                
                                        elif(h == hEd and regist['forceEd'] and output == 1):
                                           done = item['done_at']
                                           done = datetime.datetime.fromisoformat(done)
                                           done = done.strftime('%Y/%m/%d %H')
                                           dtH = dt.strftime('%Y/%m/%d %H')
                                           if(done != dtH):
                                                write_register(instr, 2, *INVERTER_COMMANDS_WRITE.get(key))
                                                time.sleep(0.1)
                                                result.append({key : instr.read_register(*INVERTER_COMMANDS_READ.get(key))})
                                                time.sleep(0.1)
                                                logger.info('auto grid off(timelimit):{h:' + str(h) + ',hEd:' + str(hEd) + ',forceEd:' + str(regist['forceEd']) + ',output:' + str(output) + '}')

                                    if result != []:
                                        post_data = {'report':1, 'user':setting_json['user'], 'mode':mode, 'result': json.dumps(result)}
                                        print('report regist')
                                        res = requests.post(url, json = post_data)
                                        print(res.text)
                                        logger.info("Regist: req:" + json.dumps(res_json) + "/res:" + res.text)
                                        
                    except Exception as e:
                        logger.exception(e)
                        print("error:", e)
                        
                #report_server inverter values
                if(ut % PROCCESS_TIMING.get('report_server') <= PROCCESS_TIMING.get('interval')):
                    data = readValues(ut - 3600, ut)
                    url = setting_json['api']['host'] + '/api/v1/log/write'
                    post_data = {'user':setting_json['user'], 'datas': data}
                    res = requests.post(url, json = post_data)
                    res_json = json.loads(res.text)
                    if(res_json['code'] == 0):
                        print('request-success')
                        logger.info("SuccessLog:" + res.text)
                        deleteValues(ut - (6 * 3600))
                    else:
                        logger.info("FaildLog:" + res.text)
                        print('error:' + res.text) 

                #report_server inverter values
                if(ut % PROCCESS_TIMING.get('report_gridprice') <= PROCCESS_TIMING.get('interval')):
                    area += 1
                    area %= len(GRID_AREA)
                    url ='https://looop-denki.com/api/prices?select_area=' + GRID_AREA[area]
                    res = requests.get(url)
                    contents = res.content
                    json_contents = json.loads(contents)
                    url = setting_json['api']['host'] + '/api/v1/regist/recordGridPrice'
#                    url = setting_json['api']['host_debug'] + '/api/v1/regist/recordGridPrice'
                    post_data = {'contents':json_contents, 'time':ut, 'area':GRID_AREA[area]}
                    res = requests.post(url, json = post_data)
                    print(res)
                    
                time.sleep(PROCCESS_TIMING.get('interval'))
            else:
                print(ut % 3600)
                time.sleep(1)
                
        except Exception as e:
            logger.exception(e)
            print("error:", e)
            time.sleep(PROCCESS_TIMING.get('interval'))

def reboot():
    subprocess.run(["sudo", "reboot"]);
#run main
main()
