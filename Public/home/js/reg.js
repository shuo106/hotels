function ClientSideStrongPassword(value)
		{
            var num = 1;
            if (value.trim().length == 0) {
                return num;
            }
            if (value.length > 0 && value.length < 7) {
                num = 2;
                return num;
            }

            var pat1 = /[a-zA-Z]+/;
            if (pat1.test(value)) {
                ++num;
            }
            var pat2 = /[0-9]+/;
            if (pat2.test(value)) {
                ++num;
            }
            var chr = "";
            for (var i = 0; i < value.length; i++) {
                chr = value.substr(i, 1);
                if ("!@#$%^&*()_+-='\";:[{]}\|.>,</?`~".indexOf(chr) >= 0) {
                    ++num;
                    break;
                }
            }
            return num;
        }

        var cansubmit = true;

        function SetPwdStrengthEx(obj, value) {
            var ret = ClientSideStrongPassword(value);
            if (ret == 1) {
                $("passwd_power").className = "pwlv pwlv1";
            } else if (ret == 2) {
                $("passwd_power").className = "pwlv pwlv2";
            } else if (ret == 3) {
                $("passwd_power").className = "pwlv pwlv3";
            } else if (ret == 4) {
                $("passwd_power").className = "pwlv pwlv4";
            }
        }    
        var formisvalid = false;
        String.prototype.trim = function () {
            return this.replace(/(^\s*)|(\s*$)/g, "");
        }

        String.prototype.replaceAll = stringReplaceAll;

        function stringReplaceAll(AFindText, ARepText) {
            var raRegExp = new RegExp(AFindText, "g");
            return this.replace(raRegExp, ARepText)
        }

       function whenOnfocus(id, tips) {
            var e = $(id);
            if ($(id + "_value").value == "" && e.className.indexOf('frm_err') < 0) {
                // e.className = e.className.replaceAll('frm_err','');
                e.className = e.className.replaceAll('frm_ok', '');
                e.className += ' frm_focus';
                var etip = $(id + "_tip");
                etip.innerHTML = tips;
                if (id == "cr_passwd") {
                    document.getElementById("passwd_power").style.display = "block";
                }
            }
            if (id == "ext_mail") {
                $("ext_frm_tip2").style.display = "none";
                $("ext_frm_btn").style.display = "none";
                $("sub_link").disabled = "";
                $("sub_link").className = $("sub_link").className.replaceAll('inp_subdis', '');
            }
        }
		function whenOnBlur(id, tips)
         {
            var e = $(id);
            e.className = e.className.replaceAll('frm_focus','');
            var etip = $(id + "_tip");

            if (id != "cr_chkcode") {
                e.className = e.className.replaceAll('frm_err', '');
                e.className = e.className.replaceAll('frm_ok', '');
            }

            if (id == "cr_mail"){              
                var value = $("cr_mail_value").value.trim();
                var e = $(id);
                if(value == ""){
                    e.className += 'frm_err';
                    etip.innerHTML = "帐号不能为空，请输入";
                    cansubmit = false;
                }else if (value.length < 6 || value.length > 20){
                    e.className += 'frm_err';
                    etip.innerHTML = "长度需要在6-20位之间，请重新输入";
                    cansubmit = false;
                }else{
                    var re = /^[a-z0-9\-\_\.]*$/;
                   
                    if (re.test(value)) 
					{
                        if (value.length >= 6 && value.length <= 20){
							e.className = e.className.replaceAll('frm_err', '');
							e.className = e.className.replaceAll('frm_ok', '');
							e.className += ' frm_ok';
						  var myAjax = new Ajax.Request(
						  user,
						  {
							  method: 'GET',
							  parameters: "user="+value ,
							  onComplete: showResponse2
						   });
                     	} 
					 }
					else 
					  {
                        cansubmit = false;
                        e.className += ' frm_err';
                        etip.innerHTML = "限小写字母、数字、横线和下划线组成，请重新输入";
                    }
                }
            }else if (id == "hotel"){  
				 var value = $("hotel_value").value.trim();
                var key = "userid";
                if (value == "") {
                    e.className += ' frm_err';
                    etip.innerHTML = "酒店名称不能为空，请输入";
                    cansubmit = false;
                }else if (value.length > 50) {
                    e.className += ' frm_err';
                    etip.innerHTML = "酒店名称长度超过最大限制，请重新输入";
                    cansubmit = false;
                } else {
					e.className = e.className.replaceAll('frm_err', '');
				    e.className = e.className.replaceAll('frm_ok', '');
				    e.className += ' frm_ok';
               }
			}else if (id == "ext_mail"){  
				var pattern = /^([a-z0-9\-\_\.])*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
                var value = $("ext_mail_value").value.trim();
                var key = "userid";
                if (value == "") {
                    e.className += ' frm_err';
                    etip.innerHTML = "邮箱不能为空，请输入";
                    cansubmit = false;
                }else if (!pattern.test(value)) {
                    e.className += ' frm_err';
                    etip.innerHTML = "邮箱格式有误，请重新输入";
                    cansubmit = false;
                }else if (value.length > 64) {
                    e.className += ' frm_err';
                    etip.innerHTML = "邮箱长度超过最大限制，请重新输入";
                    cansubmit = false;
                } else {
                    /*cansubmit = false;                 
                    oldextuserid = value;
                    var pars = key + "=" + encodeURIComponent(value) + "&act=mail";
                    ValidateRegInfo(pars); */
					e.className = e.className.replaceAll('frm_err', '');
				    e.className = e.className.replaceAll('frm_ok', '');
				    e.className += ' frm_ok';
               }
			}else if (id == "cr_passwd"){
                if ($('cr_passwd_value').value == "") {
                    e.className += ' frm_err';
                    etip.innerHTML = "密码不能为空，请输入";
                    cansubmit = false;
                } else if ($('cr_passwd_value').value.length < 6 || $('cr_passwd_value').value.length > 16) {
                    e.className += ' frm_err';
                    etip.innerHTML = "长度需要在6-16位之间，请重新输入";
                    cansubmit = false;

                } else {
                    e.className = e.className.replaceAll('frm_err', '');
                    e.className = e.className.replaceAll('frm_ok', '');
                    e.className += ' frm_ok';
                }
            }else if (id == "cr_repasswd"){
                if ($("cr_repasswd_value").value != "" && ($("cr_passwd_value").value != $("cr_repasswd_value").value)) {
                    e.className += ' frm_err';
                    etip.innerHTML = "两次输入的密码不一致，请重新输入";
                    cansubmit = false;
                } 
                else 
                {
                    if ($("cr_repasswd_value").value != "") {
                        e.className = e.className.replaceAll('frm_err', '');
                        e.className = e.className.replaceAll('frm_ok', '');
                        e.className += ' frm_ok';
                    }
                }
            } /*else if (id == "cr_name") {
                var value = $("cr_name_value").value.trim();
                if (value.trim() == "") {
                    cansubmit = false;
                    e.className += ' frm_err';
                    etip.innerHTML = "姓名不能为空，请输入";
                } else {
                    var reg = /^[\u4e00-\u9fa5]+$/i;
                    if (!reg.test(value)) {
                        e.className += ' frm_err';
                        etip.innerHTML = "姓名必须是汉字且字数为2-6位";
                        cansubmit = false;
                    } else {
                        if (value.length > 6 || value.length < 2) {
                            e.className += ' frm_err';
                            etip.innerHTML = "姓名必须是汉字且字数为2-6位";
                            cansubmit = false;
                            //$('cr_name_value').focus();
                        } else {
                            e.className = e.className.replaceAll('frm_err', '');
                            e.className = e.className.replaceAll('frm_ok', '');
                            e.className += ' frm_ok';
                        }
                    }
                }
            }*/else if(id == "verify"){
				 var pars = $("verify_value").value.trim();
				 //if( pars.length == 4){
					var myAjax = new Ajax.Request(
						  verify,
						  {
						  method: 'GET',
						  parameters: "pars="+pars ,
						  onComplete: showResponse
					  });
					
				//}
			}
		}
		  function showResponse(res){
			var e = $("verify");
			if(res.responseText == 1){
				e.className = e.className.replaceAll('frm_err', '');
				e.className = e.className.replaceAll('frm_ok', '');
				e.className += ' frm_ok';
			}else{
				cansubmit = false;
				e.className += ' frm_err';
				$("verify_tip").innerHTML= "验证码错误";
			}
		 } 
		 //用户名是否存在
		 function showResponse2(res){
			if(res.responseText == 1){
				cansubmit = false;
				var e = $("cr_mail");
				e.className = e.className.replaceAll('frm_focus', '');
				e.className = e.className.replaceAll('frm_err', '');
                e.className = e.className.replaceAll('frm_ok', '');
				
				var e = $("cr_mail");
				var etip = $( "cr_mail_tip");
				e.className += ' frm_err';
				etip.innerHTML = "该用户名已经有人注册过了";
			}
		 }
		function checkinput() {
				var e = $("cr_mail");
				e.className = e.className.replaceAll('frm_focus', '');
				
                var value = $("cr_mail_value").value.trim();
                var e = $("cr_mail");
				var etip = $( "cr_mail_tip");
				 var re = /^[a-z0-9\-\_\.]*$/;
                if (value == ""){
                    e.className += ' frm_err';
                    etip.innerHTML = "帐号不能为空，请输入";
                    return  false;
                }
				if(!re.test(value)){
				    return false; 
                }
				
				
				
				
				var pw = $("cr_passwd");
				pw.className = pw.className.replaceAll('frm_focus', '');
				var pw_etip = $("cr_passwd_tip");
				if ($('cr_passwd_value').value == "") {
                    pw.className += ' frm_err';
                    pw_etip.innerHTML = "密码不能为空，请输入";
                    return false;
                }
				var repw = $("cr_repasswd");
				repw.className = repw.className.replaceAll('frm_focus', '');
				var repw_etip = $("cr_repasswd_tip");
                if ($("cr_passwd_value").value != $("cr_repasswd_value").value) {
                    repw.className += ' frm_err';
                    repw_etip.innerHTML = "两次输入的密码不一致，请重新输入";
                    return false;
                } 

					
				var hotel = $("hotel");
				hotel.className = hotel.className.replaceAll('frm_focus', '');
				var hotel_etip = $("hotel_tip");
				var h_value = $("hotel_value").value.trim();
                if (h_value == "") {
                    hotel.className += ' frm_err';
                    hotel_etip.innerHTML = "酒店名称不能为空，请输入";
                    return false;
                }
				var email = $("ext_mail");
				email.className = email.className.replaceAll('frm_focus', '');
				var email_etip = $("ext_mail_tip");
				var e_value = $("ext_mail_value").value.trim();
                if (e_value == "") {
                    email.className += ' frm_err';
                    email_etip.innerHTML = "邮箱不能为空，请输入";
                    return false;
                }
				var verify = $("verify");
				verify.className = verify.className.replaceAll('frm_focus', '');
				var verify_etip = $("verify_tip");
				var e_value = $("verify_value").value.trim();
                if (e_value == "") {
                    verify.className += ' frm_err';
                    verify_etip.innerHTML = "请输入验证码";
                    return false;
                }
				
			return true;
		  
        }