window.onload = function () {
    // tootip内容
    // tab
    $(".weibo").click(function(){
        window.location.href="https://weibo.com/huichuangyenet";
    });
    $(".qq").click(function (){
        let qqnum = $(this).data('qq');
        window.location.href="tencent://message/?uin"+qqnum+"=&Site=http://vps.shuidazhe.com&Menu=yes";
    });
    $( "#tabs" ).tabs({
        collapsible: true
    });

};

window.onload = function () {
    // tootip内容
    $( "#serve" ).tooltip({
        tooltipClass: 'tootip-box',
        position: {
            // my: 'left center'
        },
        content: function () {
            return "<div style='width:100px; height: 100px; text-align: center'><img style='width: 90px;height:70px;background-color:#111111;margin:0 auto;' src='/uploads/20210526/82b4880e634cd1d6e54c6c42ea939964.png'/><p style='text-align: center;'>扫码联系客服</p></div>";
        }
    });
    $( "#tel" ).tooltip({
        tooltipClass: 'tootip-box',
        position: {
            at: 'right center'
        },
        content: function () {
            return "<div style='width:100px; height: 100px; text-align: cneter'><img style='width: 90px;height:70px;background-color:#111111;margin:0 auto;' src=''/><p style='text-align: center;'>Hint</p></div>";
        }
    });
    $( "#mini" ).tooltip({
        tooltipClass: 'tootip-box',
        position: {
            // my: 'left center'
        },
        content: function () {
            return "<div style='width:100px; height: 100px; text-align: cneter'><img style='width: 90px;height:70px;background-color:#111111;margin:0 auto;' src=''/><p style='text-align: center;'>Hint</p></div>";
        }
    });
    // tab
    $( "#tabs" ).tabs({
        collapsible: true
    });
};