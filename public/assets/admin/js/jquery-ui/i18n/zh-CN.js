/* Chinese initialisation for the jQuery UI date picker plugin. */
/* Written by Cloudream (cloudream@gmail.com). */
jQuery(function($){
    $.datepicker.regional['zh-CN'] = {
        closeText: 'Жабу',
        prevText: '&#x3c;Алдыңғы ай',
        nextText: 'Келесі ай&#x3e;',
        currentText: 'Бүгін',
        monthNames: ['Қаңтар','Ақпан','Наурыз','Сәуір','Мамыр','Маусым',
        'Шілде','Тамыз','Қырқүйек','Қазан','Қараша','Желтоқсан'],
        monthNamesShort: ['01','02','03','04','05','06',
        '07','08','09','10','11','12'],
        dayNames: ['Жексенбі','Дүйсенбі','Сейсенбі','Сәрсенбі','бейсенбі','Жұма','Сенбі'],
        dayNamesShort: ['жс','дс','сейс','сс','бс','жм','сб'],
        dayNamesMin: ['7','1','2','3','4','5','6'],
        dateFormat: 'yy-mm-dd', firstDay: 1,
        isRTL: false};
    $.datepicker.setDefaults($.datepicker.regional['zh-CN']);
});
