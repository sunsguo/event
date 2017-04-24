jQuery(function($){
    var processFile = "assets/inc/ajax.inc.php";
    //创建模态窗口的功能函数
    var fx = {
        //检查模态窗口是否存在，如果存在，则返回该窗口，否则创建一个并返回
        "initMOdal" : function(){
            //如果没有元素匹配则长度等于0
            //property with return 0
            if($('.modal-window').length == 0){
                //创建一个div，为他添加一个class，然后将他追加到body中
                return $('<div></div>')
                                .hide()
                                .addClass('modal-window')
                                .appendTo('body');
            }else{
                return $('.modal-window');
            }
        },
        //淡出此窗口，并从dom中删除
        "boxout" : function(event){
            if(event != undefined){
                event.preventDefault();
            }
            $('a').removeClass('active');
            $('.modal-window, .modal-overlay').fadeOut('slow', function(){
                $(this).remove();
            });
        },
        "boxin" : function(data, modal){
            //为页面创建一个覆盖层，并为其添加一个class 和 事件处理函数然后将其追加到body
            $('<div></div>')
                .hide()
                .addClass('modal-overlay')
                .click(function(event){
                    fx.boxout(event);
                }).appendTo('body');
            //将数据载入到模态窗口，并将他追加到body
            modal.hide().append(data).appendTo('body');
            //淡入模态窗口和覆盖层
            $('.modal-window, .modal-overlay').fadeIn('slow');
        },
        //保存之后将新活动添加到日历上
        "addEvent" : function(data, formData){
            data = $.trim(data);
           if(data > 0){
                //将查询字符串转换成一个对象
            var entry = fx.deserialize(formData);
            //为当前月份生成一个date对象
            cal = new Date(NaN);
            //为新活动生成一个Date对象
            event = new Date(NaN);
            //提取事件日期，月份，年份

            date = entry.event_start.split(' ')[0];
            edate =  date.split('-');
            //useDate 格式 yyyy-mm
            cdate = $('h2').attr('id').split('-');
            cal.setFullYear(cdate[1], cdate[2], 1);
            event.setFullYear(edate[0], edate[1], edate[2]);
            //由于日期对象使用gmt时间，我们需要调整时区偏移量
            //event.setMinutes(event.getTimezoneOffset());
            //如果月份年份都相符，则开始处理，把新活动添加到日历
            if(cal.getFullYear() == event.getFullYear() && cal.getMonth() == event.getMonth()){
                var day = String(event.getDate());
                day = day.length == 1 ? "0" + day : day;
                $("<a>")
                    .hide()
                    .attr('href', 'view.php?event_id=' + data)
                    .text(entry.event_title)
                    .insertAfter($('strong:contains('+day+')'))
                    .delay(1000)
                    .fadeIn('slow');
           }
           }else{
               
           }
        },
        "deserialize" : function(str){
            var data = str.split("&");
            //声明要用到的变量
            var pairs = [], entry = {}, key, val;
            for(x in data){
                pairs = data[x].split("=");
                key = pairs[0];
                val = pairs[1];
                entry[key] = fx.urldecode(val);
            }
            return entry;
        },
        "urldecode" : function(str){
            //将加号置换回空格
            var converted = str.replace(/\+/g, " ");
            //解码其他任意的编码字符
            return decodeURIComponent(converted);
        },
        "removeevent" : function(){
            //删除所有拥有class active 的活动
            $('.active').fadeOut('slow', function(){
                $(this).remove();
            });
        }
    };

   $('li>a').live('click', function(event){
       //阻止此连接载入view.php
       event.preventDefault();
       //为连接添加active class
       $(this).addClass('active');

       var data = $(this)
                        .attr('href')
                        .replace(/.+?\?(.*)$/, "$1");
        //检查窗口是否存在，若存在则选中它，否则创建一个
        modal = fx.initMOdal();
        $('<a>')
                .html('&times;')
                .addClass('modal-close-btn')
                .attr('href', '#')
                .click(function(event){
                    //删除窗口
                     fx.boxout(event);
               // $('.modal-window').hide();
                }).appendTo(modal);
        $.ajax({
            type: "POST",
            url: processFile,
            data: 'action=event_view&' + data,
            success: function(data){
                fx.boxin(data, modal)          
            },
            error: function(msg){
                modal.append(msg);
            }
        });
       //输出一行日志信息，证明脚本确实工作了
       console.log(data);
   });
//使用ajax技术加载表单
    $(".admin-options form, .admin").live('click', function(event){
           event.preventDefault();
           //设定表单提交action
           var action = $(event.target).attr('name') || 'edit_event';
           id = $(event.target).siblings("input[name=event_id]").val();
           id = (id != undefined) ? "&event_id=" + id : "";
            $.ajax({
                type: "POST",
                url: processFile,
                data: 'action=' + action + id,
                success: function(data){
                    //隐藏这个表单
                    var form = $(data).hide();
                    modal = fx.initMOdal().children(":not(.modal-close-btn)").remove().end();
                    fx.boxin(null, modal);
                    //将表单载入窗口
                    form.appendTo(modal).addClass("edit-form").fadeIn('slow');      
                },
                error: function(msg){
                    alert(msg);
                }
             });
           console.log("表单加载成功！");
       });
   //无修改刷新活动
       $(".edit-form input[type='submit']").live('click', function(){
           //阻止表单提交
           event.preventDefault();
           remove = false;
           //序列话表单数据，以便用于$.ajax()
           var formData  = $(this).parents('form').serialize();
           //保存提交按钮的值
           var submitVal = $(this).val().trim();
           //若是追加表单则追加一个action
           if($(this).attr('name') == 'confirm_delete'){
               formData += '&action=confirm_delete&confirm_delete=' + encodeURIComponent(submitVal); 
               if(submitVal == 'Yes, Delete It'){
                   remove = true;
               }
           }
           //将表单数据发往处理程序
            $.ajax({
                type: "POST",
                url: processFile,
                data: formData,
                success: function(data){
                    alert('success');
                    if(remove === true){            //删除活动有问题
                        fx.removeevent();
                    }
                   fx.boxout();
                   //如果是新活动则添加到日历
                   if($('[name=event_id]').val().length == 0 && remove === false){
                        fx.addEvent(data, formData); 
                   } 
                },
                error: function(msg){
                    alert('fail');
                     fx.boxout();                    //alert(msg);
                }
             });
           console.log("event is saved!") ;
       });

       //让cancel 按钮的行为，与close按钮一致，并淡出模态窗口和覆盖层
       $(".edit-form a:contains('cancel')").live('click', function(){
           fx.boxout(event);
       });
});