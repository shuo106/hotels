// JavaScript Document

function getEid(id){

    return document.getElementById(id);               //获取指定的div元素

}

function newNode(param){

    return document.createElement(param);             //创建元素

}

function newTextNode(param){

    return document.createTextNode(param);           //创建元素内容

}

function scrollDiv(){

    var dest=getEid("scrollMsg");                   //获取要显示滚动内容的div

    var newStr=newTextNode(new Date().toLocaleString()+":知识改变命运，科技推动发展！");                                                       //显示的滚动信息

    var span=newNode("span");                            //创建span元素

    span.appendChild(newStr);                            //在sapn中添加显示信息

    dest.appendChild(span);                              //将span添加到div中

    scrollMsg.scrollTop+=10000;                     //滚动

    setTimeout("scrollDiv()",2000);                  //设置定时器定时滚动

}

window.onload=scrollDiv;