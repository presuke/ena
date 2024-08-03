import BtnAdd from '../image/control/circle-add.svg';
import BtnRemove from '../image/control/circle-remove.svg';
import ImgC0 from '../image/role/0.png';
import ImgC1 from '../image/role/1.png';
import ImgC2 from '../image/role/2.png';
import ImgC3 from '../image/role/3.png';
import ImgC4 from '../image/role/4.png';
import ImgC5 from '../image/role/5.png';
import ImgC6 from '../image/role/6.png';
import ImgC7 from '../image/role/7.png';
import ImgC8 from '../image/role/8.png';
import ImgC9 from '../image/role/9.png';
import SePush from '../se/push.mp3';
import SeSuccess from '../se/success.mp3';
import SeError from '../se/error.mp3';
import SeClock from '../se/clock.mp3';
import SeJail from '../se/jail.mp3';
import SeWin from '../se/win.mp3';
import SeConsider from '../se/consider.mp3';
import SeSafe from '../se/safe.mp3';
import SeFreedom from '../se/freedom.mp3';
import SeJailAndFreedom from '../se/jailandfreedom.mp3';

export default {
	data:{
        roles: [
            {
                id: 0,
                name:'村人',
                shortName:'人',
                description:'特殊能力を持ちません。',
                img: ImgC0,
                color: 'gray',
                num: 0,
                power: false,
                team: 0,
                command:[]
            },
            {
                id: 1,
                name:'人狼',
                shortName:'狼',
                description:'襲撃対象を投獄することができます。（狩人に守られる、増税眼鏡に国会へ避難されると襲撃が失敗します。）別の人狼が襲撃したプレイヤーを知ることができます。常に、人狼と裏切り者と吸血鬼のプレイヤーを把握できます。',
                img: ImgC1,
                color: 'red',
                num: 0,
                power: true,
                team: 1,
                command:[]
            },
            {
                id: 2,
                name:'狩人',
                shortName:'狩',
                description:'指定したプレイヤーを人狼の襲撃から守ることができます。自分を守ることや2日連続で同じプレイヤーを守ることはできません。',
                img: ImgC2,
                color: 'green',
                num: 0,
                power: false,
                team: 0,
                command:[]
            },
            {
                id: 3,
                name:'占い師',
                shortName:'占',
                description:'指定したキャラクターの正体を見破ることができます。ただし、裏切り者を占うと村人だと判定してしまいます。',
                img: ImgC3,
                color: 'magenta',
                num: 0,
                power: false,
                team: 0,
                command:[]
            },
            {
                id: 4,
                name:'霊媒師',
                shortName:'霊',
                description:'投票で投獄されたキャラクターの正体を見破ることができます。',
                img: ImgC4,
                color: 'cyan',
                num: 0,
                power: false,
                team: 0,
                command:[]
            },
            {
                id: 5,
                name:'裏切者',
                shortName:'裏',
                description:'特殊な能力はありませんが、人狼が勝利すると勝利します。',
                img: ImgC5,
                color: 'orange',
                num: 0,
                power: false,
                team: 1,
                command:[]
            },
            {
                id: 6,
                name:'皇帝',
                shortName:'帝',
                description:'常に、全てのキャラクターの正体を把握することができます。',
                img: ImgC6,
                color: 'gold',
                num: 0,
                power: true,
                team: 0,
                command:[]
            },
            {
                id: 7,
                name:'吸血鬼',
                shortName:'鬼',
                description:'襲撃すると襲撃対象と役割が入れ替わります。（襲撃対象が投獄されることはありません。）人狼が勝利すると勝利します。',
                img: ImgC7,
                color: 'purple',
                num: 0,
                power: false,
                team: 1,
                command:[]
            },
            {
                id: 8,
                name:'増税眼鏡',
                shortName:'税',
                description:'検討すると50％の確率で人狼の襲撃から保身しますが、声を出してしまうことがあります。',
                img: ImgC8,
                color: 'darkblue',
                num: 0,
                power: false,
                team: 0,
                command:[]
            },
            {
                id: 9,
                name:'天使',
                shortName:'天',
                description:'投獄されたプレイヤーを救出することができます。',
                img: ImgC9,
                color: 'lightyellow',
                num: 0,
                power: false,
                team: 0,
                command:[]
            },
        ],
        actions:{
            vote: 'vote',
            attack: 'attack',
            save: 'save',
            sleep: 'sleep',
            predict: 'predict',
            expose: 'expose',
            consider: 'consider',
            revive: 'revive',
            change: 'change',
            freedom: 'freedom',
            confirmVoteResult: 'confirmVoteResult',
            confirmActionResult: 'confirmActionResult',
            confirmFreedomResult: 'confirmFreedomResult',
        },
        times:{
            0: '夕刻',
            1: '夜',
            2: '深夜',
            3: '朝',
            4: '昼',
        },
        btnAdd: BtnAdd,
        btnRemove: BtnRemove,
        se:{
            Success: new Audio(SeSuccess),
            Error: new Audio(SeError),
            Push: new Audio(SePush),
            Clock: new Audio(SeClock),
            Jail: new Audio(SeJail),
            Win: new Audio(SeWin),
            Consider:new Audio(SeConsider),
            Safe:new Audio(SeSafe),
            Freedom:new Audio(SeFreedom),
            JailAndFreedom:new Audio(SeJailAndFreedom),
        }
	},
    methods:{
        getAssetPath(file){
            return new URL(`../assets/${file}`, import.meta.url).href
        },
    }
};