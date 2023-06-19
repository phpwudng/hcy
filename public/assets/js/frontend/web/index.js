window.onload = function () {
    // tootip内容
    // tab
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

};
