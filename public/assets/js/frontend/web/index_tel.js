window.onload = function () {
    $(document).tooltip();
    var mySwiper = new Swiper('.swiper-container', {
        autoplay: false,
        loop: true,
        slidesPerView : 4,
        slidesPerGroup : 4,
        navigation: {
            nextEl: '.index-wrap-list-item-right-left-icon',
            prevEl: '.index-wrap-list-item-right-right-icon',
        }
    });
    var peopleSwiper = new Swiper('.people-wrapper', {
        autoplay: false,
        loop: true,
        slidesPerView : 4,
        slidesPerGroup : 4,
        navigation: {
            nextEl: '.index-wrap-list-item-right-left-icon',
            prevEl: '.index-wrap-list-item-right-right-icon',
        }
    });
    var indexWrap = document.querySelector('.index-wrap');
    var domTop = $('#scroll-top');
    domTop.click(function (e) {
        indexWrap.scrollTop = 0;
    });
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
            return "<div style='width:100px; height: 100px; text-align: center'><img style='width: 90px;height:70px;background-color:#111111;margin:0 auto;' src=''/><p style='text-align: center;'>Hint</p></div>";
        }
    });
    $( "#mini" ).tooltip({
        tooltipClass: 'tootip-box',
        position: {
            // my: 'left center'
        },
        content: function () {
             return "<div style='width:100px; height: 100px; text-align: center'><img style='width: 90px;height:70px;background-color:#111111;margin:0 auto;' src=''/><p style='text-align: center;'>Hint</p></div>";
        }
    });
    $('#tabs div.add').css('display', 'block');
    $('#tabs-wrap ul li').click(function (e) {
        var className = e.currentTarget.className;
        $('#tabs-wrap ul li').removeClass('active');
        $(this).addClass('active');
        $('#tabs div.tab').css('display', 'none');
        $('#tabs div.' + className).css('display', 'block');
    });
    $('#problem div.concat').css('display', 'block');
    $('#problem-tabs ul li').click(function (e) {
        var className = e.currentTarget.className;
        $('#problem-tabs ul li').removeClass('active');
        $(this).addClass('active');
        $('#problem div.problem-content').css('display', 'none');
        $('#problem div.' + className).css('display', 'block');
    });
};