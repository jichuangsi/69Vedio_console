// window.onload = function () {
setHTML();

        // var u = navigator.userAgent;
        // var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
        // var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        // // alert('是否是Android：'+isAndroid);
        // // alert('是否是iOS：'+isiOS);
        // if(isAndroid){
        //     document.write("<link rel='stylesheet' href='./assets/style/reset_android.css'>")
        // }else if(isiOS){
        //     document.write("<link rel='stylesheet' href='./assets/style/reset_ios.css'>")
        // }

        
// 为了在pc端更好的去调试
// resize 事件 表示调整大小触发事件
onresize = function () {
setHTML();
}

function setHTML() {
// 基础值
const baseVal = 100;
// 设计稿的宽度
const pageWidth = 750;
// 要适配的屏幕的宽度
let screenWidth = document.querySelector("html").offsetWidth;
// 要设置的fontsize
let fontsize = screenWidth * baseVal / pageWidth;
console.log(screenWidth)
console.log(fontsize)
// 设置到html标签的中
document.querySelector("html").style.fontSize = fontsize + "px";

}
// }