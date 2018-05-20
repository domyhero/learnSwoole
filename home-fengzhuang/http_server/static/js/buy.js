$(function(){
	   //点击展开关闭
	    var mark=0;//做一个判断如果var==0   记住全局
	   $('.close').click(function(){    //查找点击事件把right的宽度改变   .close的位置改变
	     
		  if(mark==0){                                 //如果mark==0
		       $('.right').animate({'width':"200px"});  
			    $('.close').animate({'right':"200px"});   
			    $('.buy  ').fadeIn(100);

		         mark=1;//如果mark==1
		       }else{
                    $('.right').animate({'width':"0px"});  
			        $('.close').animate({'right':"0px"});  
					
					$('.buy  ').fadeOut(100);  //渐隐
		            mark=0;  //返回mark=0
		      } });
	//点击展开关闭

var arr=[];     
var  price=0;
$('.center p span').click(function(e){
        
         var img=$(this).parent().parent().find('img').attr('src');   
         var  title=$(this).parent().parent().find(".ti").text();         
		  price=$(this).parent().parent().find(".price b").text();   
          var main=$(this).parent().parent().attr('main');  
		
	           $('.right dl').each(function(){ 
	        
				 arr.push($(this).find('dt').attr('main')); 
		     });
	      
  //使用$.inArray(main,arr)判断数组是否在购物车
  if($.inArray(main,arr)!=-1){
	              var num=$("dt[main="+main+"]").parent().find('.num b').text();  //获得num的值   数量
				  var total=0;
                  num= parseInt(num); //数量转变成int数值
				 
				   price= parseInt(price); //价格转变成int数值			
                   total=(num*price)+price;  //它的总价格
				  add(e);   //调用插件
                $("dt[main="+main+"]").parent().find('.num b').text((num+1)); //找到并且让它数量增加
				 $("dt[main="+main+"]").parent().find('.pri b').text((total)); //找到并且让它价格增加
                 arr=[]; //让数组为空，为下一次运行做准备
 	 }else{
		 var str="<dl ><dt  main="+main+"><img src="+img+" width='60px'   hieght='60px'/><span class='yichu'>x</span></dt>";
		 var str=str+"<dd>"+title+"</dd><dd class='color'><font class='pri'>￥<b>"+price+"</b></font><span class='num'><b>1</b></span></dd></dl>";
	    $('.right').append(str);

	    var hidd="<input type='hidden' name='title' value='"+title+"'><input type='hidden' name='price' value='"+price+"'>";
	    $('.right').append(hidd);
	     arr=[];
	   add(e);
	 }
		 

});

//移除商品开始 
$(document).on('click', '.yichu', function() {
    //function code here.
	$(this).parent().parent().fadeOut('slow',function(){
	$(this).remove();        
	});
});
//移除商品结束


//插件引用
  function  add(e){

  	//找到目标点

     var offset = $(".close").offset(); //知道购物车在哪里    购物车位置  offset()方法获取匹配元素在当前窗口的相对偏移。
     //alert(offset.left);  测试
	 var _this = $(e.target); // e.target不会变化，它永远是直接接受事件的目标DOM元素； 就是点击了加入购物车的那件物品
	 var src = _this.parent().parent().find("img").attr("src"); //找到图片地址
	 //设置我们的抛物线的样式
	  var flyer =$("<img src='"+src+"' class='fly' />");
	  flyer.fly({
	      start:{                   //开始位置
			  left:e.clientX , //获取点击购物车的XY轴
		      top:e.clientY ,
		   },
		  end:{
			  top:offset.top,
			  left:offset.left, //购物车的位置    //结束位置
              width:50,
			  height:50,
		   },
		  onEnd:function(){
		  flyer.fadeOut("slow",function(){
		  $(this).remove();  // class用完就丢
		  });
		  }
	  
	  });
	}



});
