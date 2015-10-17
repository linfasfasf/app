/*
创建：陈德昭 2012-12-11
基础函数
*/

//头部切换效果函数
function headerTab(){
	$("#main_nav").find(".nav_itme").hover(function(e){
		$(this).addClass("nav_hover");
		$(this).find(".nav_itme_selectlist").show();
		
	},
	function(){
		$(this).removeClass("nav_hover");
		$(this).find(".nav_itme_selectlist").hide();
	});
};

function sideMenuTab(){
	$("#side_menu .menu_item .hd b").bind("click",function(e){
		var menuitem = $(this).parents(".menu_item");
		var bd = menuitem.find(".bd").eq(0);
		$("#side_menu .menu_item").each(function(index, element) {
			if($(menuitem).index() != index){
				$(this).find("b").removeClass("up");
				$(this).find(".bd").slideUp("fast");
			}
			
		});
		bd.slideToggle("fast");
		$(this).toggleClass("up");
	});
	
	$("#side_menu .menu_item .hd a").bind("click",function(e){
		var menuitem = $(this).parents(".menu_item");
		$("#side_menu .menu_item .curr").removeClass("curr");
		$("#side_menu .menu_item .hover").removeClass("hover");
		$("#side_menu .menu_item .hd b").removeClass("up");
		$("#side_menu .menu_item").each(function(index, element) {
			if($(menuitem).index() != index){
				$(this).find(".curr").removeClass("curr");
				$(this).find(".hover").removeClass("hover");
				$(this).find("b").removeClass("up");
				$(this).find(".bd").slideUp("fast");
			};
		});
		$(this).parent().addClass("curr");
		$(this).parent().find("b").addClass("up");
		$(this).parents(".menu_item").find(".bd").slideDown("fast");
	});
	
	$("#side_menu .menu_item li a").bind("click",function(e){
		$("#side_menu .menu_item .curr").removeClass("curr");
		$(this).parent().addClass("curr");
		$("#side_menu .menu_item .hover").removeClass("hover");
		$(this).parents(".menu_item").find(".hd").addClass("hover");
	});
};


//输入框相关，输入框提示信息	
function inputCueInfo(id,s){
	if(id.value == s){
		id.value = "";
		id.style.color = "#666";
	}		
	id.onblur = function(){
		if(id.value == "") {
			id.value = s;
			id.style.color = "#999";
		}
	}
}



/* ==================== 日历排期 ===================== */

//日期格式化
Date.prototype.format = function(format)//.format("yyyy-MM-dd")
{
    var o =
    {
        "M+" : this.getMonth()+1, //month
        "d+" : this.getDate(),    //day
        "h+" : this.getHours(),   //hour
        "m+" : this.getMinutes(), //minute
        "s+" : this.getSeconds(), //second
        "q+" : Math.floor((this.getMonth()+3)/3),  //quarter
        "S" : this.getMilliseconds() //millisecond
    }
    if(/(y+)/.test(format))
    format=format.replace(RegExp.$1,(this.getFullYear()+"").substr(4 - RegExp.$1.length));
    for(var k in o)
    if(new RegExp("("+ k +")").test(format))
    format = format.replace(RegExp.$1,RegExp.$1.length==1 ? o[k] : ("00"+ o[k]).substr((""+ o[k]).length));
    return format;
}

function dateToStr(obj){
	var dateArray = new Array()
	for( var i=0;i<obj.length; i++){
		dateArray.push(String(obj[i].format("yyyy-MM-dd")))
		}
	return dateArray
}


//============数据列表 操作=============
var dataList = new Object();
dataList.id = "user_list";
//
dataList.allCheck = function(){
	$("#"+dataList.id+" tr input").attr("checked","checked")
}
//
dataList.removeCheck = function(){
	$("#"+dataList.id+" tr input").removeAttr("checked")
}


//========================= 鼠标经过切换图片 =============================

function onHoverImg(e,aimId){
	if($(e.target).attr("href")){
		$("#"+aimId+" img").attr("src",$(e.target).attr("href"))
		}else if($(e.target).attr("title")){
			$("#"+aimId+" img").attr("src",$(e.target).attr("title"))
		}
	var img = $("#"+aimId+" img").show()
	if(img){
		$("#"+aimId).css({
			'position':'absolute',
				'left':'0',//e.pageX-($("#"+aimId).width()/2)
				'top':$(e.target).position().top,
				//'margin-left':($("#"+aimId).width()/2)*-1,
				'display':'inline-block'
		})
	}
}

//------------------全局下拉菜单 select_all------------------
function select_all(){
	$('.select_all').click(function(){
		var $this = $(this)
		var selectsd = $this.find('.selected')
		var select_list = $this.find(".select_list")
		select_list.width($this.width())
		$this.css('z-index',999)
		select_list.slideDown('fast', function(){
			select_list.find('a').click(function(e){
				var e= e ? e : window.event;
				//$(this).parentsUntil().find('.selected_input').val($(this).html())
				selectsd.html($(this).html()+"<s></s>")
				select_list.slideUp('fast')
				e.cancelBubble=true//ie下阻止冒泡
				e.stopPropagation()//其他浏览器下阻止冒泡
				$this.css('z-index',1)
			})
		})
		$(this).hover({}, function(){
			select_list.slideUp('fast')
			$this.css('z-index',1)
			})
		})
	}
	

//-------------------------添加组成员-----------------------------
function user_choice(obj){
	$(obj).unbind()
	$(obj).click(function(){
		if( this.className == "ch"){
			this.className = "";
		}else if(this.className != "null"){
			this.className = "ch";
		}
	})		
}
var member = new Object();
member.move = function(listA,listB,trait){
	listA = "#"+listA;
	listB = "#"+listB;
	trait = " "+trait;		
	userList = $(listA+trait);
	var obj = userList.clone();
	obj.appendTo($(listB));
	userList.remove();
	userList.addClass("null");
	user_choice(obj);
};
//member.add = function(){};	
//member.del = function(){};


//----------------------------------千分号转换------------------------------------------
function dataThourand(srcValue){
	if(typeof srcValue == 'string'){
		//去除千分号 赋值
 		return srcValue.replace(/,/g, ""); 
	}else if(typeof srcValue == 'number'){
		//加上千分号 显示
		srcValue = srcValue+''
        srcValue = srcValue.split('.');
        var last = srcValue[1];
        srcValue = srcValue[0];
		srcValue = srcValue.replace(/(\d{1,3})(?=(\d{3})+(?:$|\.))/g, "$1,"); 
        if(typeof last != 'undefined')
        {
            return srcValue + '.' + last;
        }else
        {
            return srcValue;
        }
	}
	
}


/**
 * 设置 数字或字符串 指定分隔符 后 用 指定字符 补齐指定 位数 (默认为 小数点后 用0 补齐2位)
 * 
 * @param mixed $num 要转换的数字
 * @param int count 分隔符后要补到的位数
 * @param char chars 分隔符后要补的 字符
 * @param char dec 分隔符
 */
function setZero(num, count, chars, dec)
{
    var prefix = '.';
    var i = 0;
    //设置默认值
    count = setDefault(count, 2);
    chars = setDefault(chars, '0');
    dec = setDefault(dec, '.');
    //分割字符串
    num = String(num).split(dec);
    if(typeof num[1] != 'undefined')
    {
        prefix += num[1];
        i = num[1].length;
    }
    for(; i < count; i++)
    {
        prefix += chars;
    }
    num = num[0] + prefix;
    return num;
}
/**
 * 设置默认值
 * 
 * @param args $args 要设置的变量
 * @param def $def 默认值
 */
function setDefault(args, def)
{
    if(typeof args == 'undefined')
        args = def;
    return args;
}

/**
 * 定义公用的splash : 用户提交动作后相应的弹出层
 * @param options obj
 **/
function splash(options){

    var defaults = {
        str:'5秒后将跳转到首页',
        url:'/',
        setSec:5
    }
    var options = $.extend({}, defaults, options);
    var minSeconds = options.setSec*1000;

    $('body').append('<div class="splash_wrap" ><div class="splash">'+options.str+'</div></div>');
    $('.splash_wrap').fadeIn();
    $('.setSec').html(options.setSec);
    var win_width = $(window).width();
    var left = win_width / 2-$('.splash').width()/2;
    $('.splash').css({'left':left,'top':'10%'});

    setInterval(function(){
        if(options.setSec < 0){
            return;
        }else{
            $('.setSec').html(options.setSec);
        }
        options.setSec--;
    },1000)

    setTimeout(function(){
        $('.splash_wrap').fadeOut(300,function(){
            $('body .splash_wrap').remove();
            window.location.href=options.url;
        })
    },minSeconds)
}


//----------------------日期表时间设置，今天SetDate(0)，昨天SetDate(-1)

function SetDate(day,date_box){
	if(!date_box){
		var date_box = $(".calendar").eq(0);
	}
	day = Number(day)
	var thisDate = new Date();
	var pastDate = new Date();
	if(day <= -1){
		thisDate.setDate(thisDate.getDate()-1);
		pastDate.setDate(pastDate.getDate()+day);
	}else{
		thisDate.setDate(thisDate.getDate());
		pastDate.setDate(pastDate.getDate()+day);
	}
	var dataStart =  thisDate.format("yyyy-MM-dd") //String(thisDate.getFullYear()+"-"+(thisDate.getMonth()+1)+"-"+thisDate.getDate());
	var dateEnd = pastDate.format("yyyy-MM-dd") //String(pastDate.getFullYear()+"-"+(pastDate.getMonth()+1)+"-"+pastDate.getDate());
	if(day <= -1){
		//过去时间
		$(date_box).DatePickerSetDate([dateEnd,dataStart],false);
		return [dateEnd,dataStart]
	}else{
		//未来时间
		$(date_box).DatePickerSetDate([dateEnd,dataStart],false);
		return [dateEnd,dataStart]
	}

}

/*tab switch function*/
function tabInit(tabC,afterSW){
	var items = tabC.find(".tab_item");
	items.each(function(index,ele){
		$(ele).on("click",function(e){
			for(var i=0;i<items.length;i++){
				$(items[i]).removeClass("curr");
				$($(items[i]).attr("data-tab")).hide();
			}
			$(this).addClass("curr");
			//$($(this).attr("data-tab")).show();
			$($(this).attr("data-tab")).show();
			afterSW && afterSW(this);
		});
	});
};
//搜索框 清空按钮
function searchClean(callback){
	$(".icon_clean").bind("click",function(){
		$(this).parent().find("input").val("").focus();
		$(this).hide();
	});
	if(callback){ callback(); };
}


/*
*钢琴键切换块
*.tab_blocks
*/
function tab_block(btnClass,conClass){
	$(".tab_block").each(function(index, element) {
		var btns = $(this).find(".tab_btn");
		var cons = $(this).find(".block_bd");
		btns.each(function(index, element) {
			btns.click(function(e){
				var index = $(this).index()-1;
				btns.removeClass(btnClass);
				$(this).addClass(btnClass);
				
				cons.removeClass(conClass);
				cons.eq(index).addClass(conClass);
				return false
			})
		});
	});
};


//----------------------------------2013.3.29 表头固定顶部效果----------------------------------
function theadPopuTop(tableObj,_ClassName) {
	var key = parseInt(1000*Math.random());
	var idName = "table_th_popu"+key;
	var id = "#"+idName
	var Objthead = tableObj.find("thead").eq(0);
	var th_w = tableObj.width();
	var th_h = Objthead.height();
	var th_y = tableObj.offset().top;
	var th_x = tableObj.offset().left;
	var table_h = tableObj.height();
	var th = tableObj.find("thead th"); 
	var div = document.createElement("div");
	div.id = idName;
	$("body").eq(0).append(div);
	$(id).addClass(_ClassName);
	$(id).append("<table class='applist' width='100%'> <colgroup></colgroup> <thead></thead> <tbody></tbody></table>");
	$(id+" table").eq(0).each(function(index, element) {
		$(this).find("colgroup").append(tableObj.find("colgroup").html());
		$(this).find("thead").append(Objthead.html());
		$(this).find("tbody").append(tableObj.find("tr").eq(1).html());
	});
	$(id+" table thead th").each(function(index, element) {
		var w = th.eq(index).width();
		$(this).css({"width":w+"px"});
	});
	$(id).hide();
	$(window).scroll(function(){
		var sy = $(this).scrollTop();
		if(sy>=th_y && sy<=(th_y+table_h) ){
			$(id).css({"width":th_w,"height":th_h+2,"position":"fixed","top":"0","left":th_x+"px","font-size":"14px","overflow":"hidden"});
			$(id).show();
		}else{
			$(id).hide();
		};
	});
};

window.onload = function(){
	//头部切换
	headerTab();
	
	if($("#side_menu")){
		//侧栏切换
		sideMenuTab();
	};
	if($(".select_all")){
		//全局下拉菜单
		select_all()
	};
	
	
	//---------------------弹出框 关闭按钮----------------------
	$('.popup_body .close').click(function(){
		$(this).parentsUntil().find('.popup_body').fadeOut();
	})
	
	$(".button_gotop").hide();
	
};
$(window).scroll(function(){
	var h = $("body").height();
	var y = $(window).scrollTop();
	if(y>h){
		$(".button_gotop").show();
	};
})

