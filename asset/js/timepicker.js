/**
*创建者：陈德昭
*创建时间：2012-03-20
*修改：2012-04-19
*修改：2012-05-06
*修改：2012-05-08
**/
(function ($) {	
	var TimePicker = function () {
		var	ids = '#timepicker',
			week =[ "周日","周一", "周二", "周三", "周四", "周五", "周六"],
			weekClassName =["Sunday", "monday","tuesday", "wednesday", "thursday", "friday", "saturday"],
			views = '<div class="timepicker"><a class="btn_prev" href="javascript:void(0);">◄</a> <a class="btn_next" href="javascript:void(0);">►</a>'
				+' <div class="timelist"> <a>00:00</a> <a>01:00</a> <a>02:00</a> <a>03:00</a> <a>04:00</a> <a>05:00</a> <a>06:00</a> <a>07:00</a> '
				+'<a>08:00</a> <a>09:00</a> <a>10:00</a> <a>11:00</a> <a>12:00</a> <a>13:00</a> <a>14:00</a> <a>15:00</a> <a>16:00</a> <a>17:00</a> '
				+'<a>18:00</a> <a>19:00</a> <a>20:00</a> <a>21:00</a> <a>22:00</a> <a>23:00</a> <span><input type="text" value="100"/></span><p>默认组日预算</p></div>'
				+'<div class="datelist">'
				+'<div class="datelist_in">'
				+' </div><p class="txt">实际组日预算</p></div><div class="dateclass hidden">'
				+'<a title="1">'+
	     		week[1]+'</a><a title="2">'+
	     		week[2]+'</a><a title="3">'+
	     		week[3]+'</a><a title="4">'+
	     		week[4]+'</a><a title="5">'+
	     		week[5]+'</a><a title="6">'+
	     		week[6]+'</a><a title="0">'+
	     		week[0]+'</a></div></div>',
			days = new Date(),
			TimeStart = "2012-2-15",
			TimeEnd = "2012-2-15",
			numDays = 7,
			minWidth = 40,
			maxWidht= numDays * minWidth,
			leftSpace = 0,
			movecol = 1,
			eventName='click',
			options = {},
			day_budget = $(ids+" .timelist .budget").val(),
			

			Debug =  function(str){
				var s = $("#debug").html()
				s = s+''+str +'<br/>'
				$("#debug").html(s)
				return s
			},

			
			/* 
			*@ 左按钮
			*/
			prev = function(e){
				e = e ? e : window.event; 
				var eObj = e.target
				$(eObj).unbind("click")
				var s = $(e.target).nextAll('.datelist').find('.datelist_in').css("left")
				s = minWidth * Math.ceil(Number(s.substring(0,[s.length-2]))/minWidth)
				leftSpace >=0 ? leftSpace : leftSpace =  s + minWidth * movecol;
				//Debug((leftSpace >=0) +":"+s)
				return $(e.target).nextAll('.datelist').find('.datelist_in').animate({
					left:leftSpace+"px"}, 
					function(){
						//Debug(leftSpace+" ; "+s)
						$(e.target).nextAll('.datelist').find('.datelist_in').css("left",leftSpace+"px")
						$(eObj).bind("click",prev)		
				  });
				},
			/* 
			*@ 右按钮
			*/	
			next = function(e){
				e = e ? e : window.event; 
				var eObj = e.target
				$(eObj).unbind("click");
				var s = $(e.target).nextAll('.datelist').find('.datelist_in').css("left")
				var w = minWidth*7
				s = minWidth* Math.ceil(Number(s.substring(0,[s.length-2]))/minWidth)
				leftSpace <= (maxWidht-w)*-1 ? leftSpace : leftSpace = s - minWidth * movecol ;
				return $(e.target).nextAll('.datelist').find('.datelist_in').animate(
					{
					left:leftSpace+"px"}, 
					function(){
						//Debug(leftSpace)
						$(e.target).nextAll('.datelist').find('.datelist_in').css("left",leftSpace+"px");						
						$(eObj).bind("click",next)
					});
				},
			/* 
			*@ 用户选择
			*/	
			choose = function(e){
				if ($(e.target).is('a') && !$(e.target).hasClass("past")) {
					$(e.target).hasClass("h")  ?  $(e.target).removeClass("h")  : $(e.target).addClass("h")
					}
				if($(e.target).parent().is('dd')){
					if($(e.target).hasClass("h") && !$(e.target).hasClass("past")) {
							$(this).html("&radic;") 
						}else if(!$(e.target).hasClass("past")){ 
							$(this).html("X")
						}
					}
				},
			
			/* 
			*@ makeChoose
			*@ 选择横
			*/
			makeChoose = function(e){
				var num = $(this).index()
				var dl_list = $(ids).find("dl")
				$(this).toggleClass("h")
				if($(this).hasClass("h")){
					for(var i=0; i<dl_list.length; i++){		
						$(dl_list[i]).find("dd a").eq(num).addClass("h")						
						if( !$(dl_list[i]).find("dd a").eq(num).hasClass("past")){ $(dl_list[i]).find("dd a").eq(num).html("&radic;") }
						}
					}else{
						for(var i=0; i<dl_list.length; i++){		
						$(dl_list[i]).find("dd a").eq(num).removeClass("h")
						if( !$(dl_list[i]).find("dd a").eq(num).hasClass("past")){ $(dl_list[i]).find("dd a").eq(num).html("X") }											
						}
					}
			},
			
			/* 
			*@apeakChoose
			*@选择列
			*/
			apeakChoose = function(e){
				var num =  $(this).parent().index()
				var dl_list = $(ids).find("dl").eq(num)
				$(dl_list).find('dt.days').toggleClass("h")
				if($(this).hasClass("h")){
					$(dl_list).find("a").each(function(){
						if( !$(this).hasClass("past") ){
							$(this).addClass("h")
							$(this).html("&radic;")
						}
					})
				}else{
					$(dl_list).find("a").each(function(){
						if( !$(this).hasClass("past") ){
							$(this).removeClass("h")
							$(this).html("X")
						}
					})
				}
			},
	
			/* 
			*@workingDayChoose
			*@按周选择
			*/
			weekDayChoose = function(day,curr){				
				var weekClass = weekClassName[0]
				switch(typeof(day)){
					case'undefined':
						alert('undefined   '+day)
					break;
					
					case'number':
						//alert(day)
						weekChoose(day,curr)
					break;
					
					case'string':
						//alert('string  '+day)
						if(day.length = 1){
							day = Number(day)
							weekChoose(day,curr)
						}else{
							var list = day.split(",")
							for(var i=0; i<list.length; i++){
								var s = Number(list[i])
								weekChoose(s,curr)
							}
						}
					break;
					
					case 'object':
					case '[object Array]':
						for(var i=0; i<day.length; i++){
							var s = Number(day[i])
							weekChoose(s,curr)
						}
					break;
					}
								
				},
	
				weekChoose = function(day,curr){
						$(ids+" dl."+weekClassName[day]+" a").each(function(){
							//curr ? $(ids+" dl."+weekClassName[day]).find("dt.weeks").addClass("choice") : $(ids+" dl."+weekClassName[day]).find("dt.weeks").removeClass("choice")
							curr ? $(this).addClass("h") : $(this).removeClass("h")
							curr && !$(this).hasClass("past") ? $(this).html("&radic;") : $(this).html("X")
						})
				},
			
				
				/* 
				*@设置：禁选项
				*/
				forbiddenChoose = function(dayObj){
					
				},
				
				/* 
				*@workingDayChoose
				*@日期格式化
				*/
				dateFormat = function(formatStr){
					var o ={
						"M+" : this.getMonth()+1, //month
						"d+" : this.getDate(),    //day
						"h+" : this.getHours(),   //hour
						"m+" : this.getMinutes(), //minute
						"s+" : this.getSeconds(), //second
						"q+" : Math.floor((this.getMonth()+3)/3),  //quarter
						"S" : this.getMilliseconds() //millisecond
					}
					if(/(y+)/.test(formatStr))
						formatStr=formatStr.replace(RegExp.$1,(this.getFullYear()+"").substr(4 - RegExp.$1.length));
					for(var k in o)
						if(new RegExp("("+ k +")").test(formatStr))
						
					formatStr = formatStr.replace(RegExp.$1,RegExp.$1.length==1 ? o[k] : ("00"+ o[k]).substr((""+ o[k]).length));
					return formatStr;
				},
				
			/* 
			*@日期字符串格式化, 2012-1-1 转 2012-01-01
			*/	
			dateStrformat = function(dateStr){
				var s = dateStr.split("-")				
				var reg =/^[0-3][0-9]/;
				!reg.test(s[1]) ? s[1] = '0'+s[1] : s[1]
				!reg.test(s[2]) ? s[2] = '0'+s[2] : s[2]
				return s[0]+"-"+s[1]+"-"+s[2]
			},
			
			/*
			*@日期字符串转日期数据
			*/
			strToNewDate = function(DateStr){
				var dateArray = DateStr.split("-")
				return new Date(dateArray[0],[Number(dateArray[1])-1],[Number(dateArray[2])])
			},
			/*
			*@处理数据
			*/
			initData = function(obj){
				var type = obj.type || 1,
					day_num = 0,
					newObj = {'type':type,time:[]};
					newObj.daybudget = obj.daybudget ? obj.daybudget: "";
				switch(type){
					//日期+周
					case "1":
						TimeStart = dateStrformat(obj.time[0]);
						TimeEnd = dateStrformat(obj.time[1]);
						var timeStart = strToNewDate(TimeStart),
						timeEnd = strToNewDate(TimeEnd)	,
						Time = strToNewDate(TimeStart);
						while(Time <= timeEnd){
							var year = Time.getFullYear();
							var month = Time.getMonth();
							var date =  Time.getDate();
							var day = Time.getDay();
							newObj.time.push({'year':year,'month':month,'date':date,'day':day});
							Time.setDate(Time.getDate()+1);
							day_num++;
						}
						day_num<= 14 ? movecol = 1 : movecol = 2;
						if(day_num > 28) movecol= 7
						break;
					//周
					case "2":
						for(var i =0;i<=6;i++){
							newObj.time.push({'day':i});
						}
						break;
					default:
						alert("传入类型错误");
						break;
				}
				if(newObj.time.length){return newObj;}else{return false;}
			},
			/*
			*@render
			*/
			initDate = function(options) {
				var type = options.type ? options.type : "1",
					timeA = options.time,
					daybudget = options.daybudget ? options.daybudget :"",
					thisDate = new Date(),
					str = '';
					console.log(options)
				for(var i=0;i<timeA.length;i++){
					var _year = timeA[i].year,
						_month = timeA[i].month+1,
						_day = timeA[i].day,
						_date = timeA[i].date;
					str += '<dl class="'+weekClassName[_day]+'">';
					str += '<dt class="weeks" title="'+_day+'">'+week[_day]+'</dt>';
					(type == "1")&&(str += '<dt class="days">'+(_month<=9?"0"+_month:_month)+"-"+(_date<=9?"0"+_date:_date)+'</dt>');
					for(var j=0; j<=23;j++){
						if(type ==1){
							var _time = new Date(_year,_month-1,_date,j);
							var classN ='';
							_time.getTime() < thisDate.getTime()?classN="past":'';
							var title = _year+"-"+(_month<=9?"0"+_month:_month)+"-"+(_date<=9?"0"+_date:_date)+","+j+":00";
							str += '<dd><a title="'+title+'" class="'+classN+'" data-hour="'+j+'">X</a></dd>';
						}else{
							str += '<dd><a title="'+_day+","+j+":00"+'" data-hour="'+j+'">X</a></dd>';
						}
					}
					str += '<dd><input type="text" value="'+daybudget+'"></dd></dl>';
				}
				$(ids+" .datelist_in").html(str);
				$(ids+" .timelist").find("input").val(daybudget);
				
				/*var weekList = $(ids+" dl").find("dt")
				Time = strToNewDate(TimeStart)
				for(var n=0; n< weekList.length; n++){
					var day = (Time.getMonth())<9? "0"+(Time.getMonth()+1) : Time.getMonth()+1
					day = Time.getDate()<=9 ? day+"-0"+Time.getDate() : day+"-"+Time.getDate()
					$(weekList[(n*2)]).parent().addClass(weekClassName[Time.getDay()])
					$(weekList[(n*2)]).html(week[Time.getDay()])
					$(weekList[(n*2)]).attr("title", Time.getDay())
					$(weekList[(n*2+1)]).html(day)					
					Time.setDate(Time.getDate()+1)
				}*/
				/*Time = strToNewDate(TimeStart)
				var dl_list = $(ids+" dl")
				for(var i=0; i<dl_list.length;i++){
					$(dl_list[i]).find("a").attr("title",function(){					
						var str = "";
						var date = $(this).parent().parent().find("dt").eq(1).html();
						var time = $(this).parent().index()-2;
						Time.getTime() < thisDate.getTime()  ? this.className = "past" : Time
						str = str +Time.getFullYear()+"-"+ date +","+time+":00";
						Time.setHours(Time.getHours()+1);
						return str
					})
				}*/				
			},
			
			/*
			*@时间转英文, 0=A,1=B
			*/
			strSwitch = function(num){
				//alert( typeof(num))
				if(typeof(num)=="number"){
					var strArray = new Array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','')
					0<= num && num <=23 ? num : num = 24;
					return strArray[num]
				}
				if(typeof(num)=="string"){
					var strArray = {'A':0,'B':1,'C':2,'D':3,'E':4,'F':5,'G':6,'H':7,'I':8,'J':9,'K':10,'L':11,'M':12,'N':13,'O':14,'P':15,'Q':16,'R':17,'S':18,'T':19,'U':20,'V':21,'W':22,'X':23}
					return strArray[num]
				}
			},
			
			/*
			*获取数据，返回JSON格式
			*
			*/
			prepareDate = function(){
				var type = options.type;
				var startDate = strToNewDate(TimeStart)
				var dataArray = new Object()
				var listObj = $(ids).find("dl")
				var day = ""
				$(ids).find("dl").each(function() {
					var s = "",num = day_budget;
					$(this).find("dd a").each(function() {
						/*(type == 1) && (s = s + strSwitch( Number( $(this).parent().index()-2) ));
						(type == 2) && (s = s + strSwitch( Number( $(this).parent().index()-1) ));*/
						if($(this).html() == "&radic;" || $(this).html() == "√"){
							s = s + strSwitch( Number( $(this).attr("data-hour")) );
						}
					});
					num = $(this).find("input[type='text']").val();
					if(s != null && typeof(s) != undefined && s!=""){
						(type == 1) && (dataArray[startDate.getFullYear()+"-"+$(this).find("dt").eq(1).html()] = new Array(s,num));
						(type == 2) && (dataArray[$(this).find("dt").eq(0).attr('title')] = new Array(s,num));
					}
					startDate.setDate(startDate.getDate()+1)
				});
				return dataArray
			},

			unEvent = function(ids){
				var obj = $(ids)
                obj.off(eventName,".timelist a");
                obj.off(eventName,"dl dd a");
                obj.off(eventName,"dl dt.days");
                obj.off(eventName,"dl dt.weeks");
			},

			addEvent = function(ids){
				var obj = $(ids)

                obj.on(eventName,'.timelist a',makeChoose);
                obj.on(eventName,'dl dd a',choose);
                obj.on(eventName,'a.btn_prev',prev);//左边按钮
                obj.on(eventName,'a.btn_next',next);//右边按钮
                obj.on(eventName,'dl dt.days',apeakChoose);//天
                obj.on(eventName,'dl dt.weeks',function(e){//周
                    var curr = true
                    e = e ? e : window.event;
                    $(e.target).hasClass("choice") ? $(e.target).removeClass("choice") : $(e.target).addClass("choice")
                    $(e.target).hasClass("choice") ? curr = true : curr = false
                    weekDayChoose(this.title,curr)
                });//周
			},

			/*
			*填充数据
			*/
			fullData = function(date,daybudget){
				console.log(date)				
				var dl_list = $(ids).find("dl")
				dl_list.each(function(index, el) {	
					var thisDay = $(this).find("a").eq(0).attr("title").split(",");					
					if(date[thisDay[0]]){
						var times = date[thisDay[0]][0]
						for(var j =0; j<times.length; j++){									
							var s = times.substring(j,j+1)									
							var obj = $(this).find("a").eq(strSwitch(s))
							//Debug(s)
							$(obj).addClass('h')
							$(obj).html("&radic;")
						}
						if(date[thisDay[0]][1] || date[thisDay[0]][1] == 0 || date[thisDay[0]][1] == '0'){
							setDayNum(date[thisDay[0]][1],$(this).find("dd input"));
						}else if(daybudget){
							setDayNum(daybudget,$(this).find("dd input"));
						};
					}
				});
			},

			/*
			*设置日预算的值
			*/
			setDayNum = function(num,obj){
				if(typeof obj=="object"){
					obj.val(num);
				}else{
					var dl_list = $(ids).find("dl");
					dl_list.each(function(index,el){
						$(this).find("dd input").eq(0).val(num);
					});
				}
			}


	//==================================== return ===================================
	
		return {
			init: function(obj){
				var radom = parseInt(Math.random()*1000)
				ids = "#timepicker"+radom;
				$(this).html(views)
				//alert(ids)
				$(this).find('.timepicker').attr("id","timepicker"+radom)
				options = initData(obj);
				/*if( !arguments[0] || !arguments[0].time[0] || !arguments[0].time[1]){				
					var date = new Date()
					Obj.time[0] = date.dateFormat("yyyy-MM-dd")
					Obj.time[1] = [date.setDate(Time.getMonth()+13)].dateFormat("yyyy-MM-dd")
					//alert(Obj.time[0]+","+Obj.time[1]);
				}*/
				/*TimeStart = dateStrformat(obj.time[0])
				TimeEnd = dateStrformat(obj.time[1])*/				
				options && initDate(options);//初始化数据	
				if(options.type == "2"){
					$(this).find('.btn_prev').hide();
					$(this).find('.btn_next').hide();
					$(this).find('.timelist').css('paddingTop','18px');
				}
				numDays = $(ids).find("dl").length;
				minWidth = $(ids+" dl").eq(0).width()
				maxWidht = numDays * minWidth
				leftSpace = 0
				
				return this.each(function(){
					addEvent(ids);
				})
				
			},
			
			DayChoose:function(day,e){				
				var curr = true
				e = e ? e : window.event;
				$(e.target).hasClass("choice") ? $(e.target).removeClass("choice") : $(e.target).addClass("choice")
				$(e.target).hasClass("choice") ? curr = true : curr = false
				return weekDayChoose(day,curr)
			},
			
			setDate:function(date,formats){
				if( !arguments[0] ){ 
					console.log("日期数据不能为空");
					return 
				}
				var startAndend = formats.startAndend ? formats.startAndend : true, //数据格式是 开始日期～结束日期
					alltime = formats.cycleTime == "all" ? true : false, //时间格式 ：全部时间段
					shiftTo = formats.shiftTo ? formats.shiftTo : undefined,
					daybudget = formats.daybudget ? formats.daybudget : "";
				console.log(typeof(shiftTo))
				switch(typeof(date)+","+typeof(shiftTo)){
					case'undefined,undefined':
					case'undefined,string':
					case'undefined,object':
						alert("您设置的日期数据不对！")
					break;
										
					case'object,undefined'://设置对应天
						fullData(date,daybudget);
					break;
					
					case'string,undefined'://设置当天全天
						//留空
					break;					
					
					case'string,string'://某天开始到某天结束
						//留空
					break;
					
					case'string,object'://0点开始~某天某点结束
						//留空
					break;
					
					case'object,string'://某天某点开始~某天23点结束
						//留空
					break;
					
					case'object,object'://某天某点开始~某天某点结束
						//留空
					break;
					}
			},
			/*
			*设置天预算
			*/
			setdaybudget:function(num,obj){
				setDayNum(num,obj);
			},

			/**/
			redaybudget:function(Default,num){
				var dl = $(ids+" dl")
				dl.find("dd input[type=text]").each(function(index, el) {
					if($(this).val() == String(Default)){
						setDayNum(num,$(this));
					};
				});
			},
			
			/*
			*获取数据，返回JSON格式
			*/
			getDate:function(){
				var json = {
					"daybudget" : $(".timepicker .timelist input").eq(0).val(),
						 "data" : prepareDate()
				}
				return json
			},
			
			/*
			*禁止选择
			*/
			forbidselect:function(){
                $(ids).off(eventName,'.timelist a');
                $(ids).off(eventName,'dl dd a');
                $(ids).off(eventName,'dl dd days');
                $(ids).off(eventName,'dl dd weeks');
				$(ids+" dl input[type=text]"+",.timelist input").attr("disabled","value").addClass('die');
			},
			
			clear: function(){
				$(this).find("dd a.h").each(function(){
						$(this).removeClass("h")
						$(this).html("X")
				});
				$(this).find(".timepicker .timelist a.h").removeClass("h")
				$(this).find(".dateclass a.choice").removeClass("choice")
			}
			
		};// return
		
	}();
	
	$.fn.extend({
		TimePicker 				: TimePicker.init,
		TimePickerSetWeek 		: TimePicker.DayChoose,
		TimePickerSetDate 		: TimePicker.setDate,
		TimePickerGetDate 		: TimePicker.getDate,
		TimePickerSetDayBudget 	: TimePicker.setdaybudget,
		TimePickerReDayBudget 	: TimePicker.redaybudget,
		TimePickerForbidSelect 	: TimePicker.forbidselect,
		TimePickerClear 		: TimePicker.clear
	});
})(jQuery);

