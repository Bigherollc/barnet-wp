jQuery(document).ready(function ($) {
    $(".about_us_menu a ").append("<i class='ico-about-us'></i>");
    var  use = $("#user_login").val();
    $(".login_user >a").text(use);
    $(".careers_menu   a  ,.sign_in_menu  a ,.login_user >a").append("<i class='icon icon-account'></i>");
    $(".samples_menu   a ").append("<i class='icon icon-sample'></i>");

});