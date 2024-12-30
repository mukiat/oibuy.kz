/**
 * 格式化时间
 * 
 * @param {String} str
 * @returns 格式化后的时间
 */
export const reportFormatDate = (str) => {
    if(!str) return ''
    var date = new Date(str)
    var time = new Date().getTime() - date.getTime() //现在的时间-传入的时间 = 相差的时间（单位 = 毫秒）
    if (time < 0) {
        return ''
    } else if ((time / 1000 < 30)) {

        return 'Жаңа'
    } else if (time / 1000 < 60) {
        return parseInt((time / 1000)) + 'сек.бұрын'
    } else if ((time / 60000) < 60) {
        return parseInt((time / 60000)) + 'мин.бұрын'
    } else if ((time / 3600000) < 24) {
        return parseInt(time / 3600000) + 'сағ.бұрын'
    } else if ((time / 86400000) < 31) {
        return parseInt(time / 86400000) + 'күн бұрын'
    } else if ((time / 2592000000) < 12) {
        return parseInt(time / 2592000000) + 'ай бұрын'
    } else {
        return parseInt(time / 31536000000) + 'жыл бұрын'
    }
}
