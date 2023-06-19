
    var tit = $('#wx-title').val(); //标题
    var img = $('#wx-img').val(); //图片
    var con = $('#wx-con').val(); //简介
    var link = $('#wx-link').val(); //链接
    document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
// 发送给好友
    WeixinJSBridge.on('menu:share:appmessage', function (argv) {
        WeixinJSBridge.invoke('sendAppMessage', {
            "appid": "123",
            "img_url": img,
            "img_width": "160",
            "img_height": "160",
            "link": link,
            "desc": con,
            "title": tit
        }, function (res) {
            _report('send_msg', res.err_msg);
        })
    });

// 分享到朋友圈
    WeixinJSBridge.on('menu:share:timeline', function (argv) {
    WeixinJSBridge.invoke('shareTimeline', {
    "img_url": img,
    "img_width": "160",
    "img_height": "160",
    "link": link,
    "desc": con,
    "title": tit
}, function (res) {
    _report('timeline', res.err_msg);
});
});
}, false)