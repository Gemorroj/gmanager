var Gmanager={_getKey:function(a){if(null==a.which&&(null!=a.charCode||null!=a.keyCode))a.which=null!=a.charCode?a.charCode:a.keyCode;return a.which},_insAtCaret:function(a,c){var b;a.focus();if("selection"in document){b=document.selection.createRange();if(b.parentElement()!==a)return;b.text=c;b.select()}else"selectionStart"in a?(b=a.selectionStart,a.value=a.value.substr(0,b)+c+a.value.substr(a.selectionEnd,a.value.length),b+=c.length,a.setSelectionRange(b,b)):a.value+=c;a.focus()},_getCaretPosition:function(a){var c,b;if("selectionStart"in a)return a.selectionStart;c=document.selection.createRange();b=a.createTextRange();a=b.duplicate();b.moveToBookmark(c.getBookmark());a.setEndPoint("EndToStart",b);return a.text.length},_setCaretPosition:function(a,c){var b;"setSelectionRange"in a?void 0!==window.opera?a.setSelectionRange(c+1,c+1):a.setSelectionRange(c,c):(b=a.createTextRange(),b.collapse(!0),b.moveStart("character",a.value.substring(0,c).replace(/\n/g,"").length+1),b.moveEnd("character",0),b.select())},number:function(a){var c=this._getKey(a);return a.ctrlKey||a.altKey||32>c?!0:/[\d]/.test(String.fromCharCode(c))},check:function(a,c,b){for(var d=0;d<a[c].length;d++)a[c][d].checked=b},checkForm:function(a,c){if(void 0===a[c])return!1;if(a[c]instanceof HTMLInputElement)return!1===a[c].checked&&window.alert(document.getElementById("chF").innerHTML),a[c].checked;for(var b=0;b<a[c].length;b++)if(!0===a[c][b].checked)return!0;window.alert(document.getElementById("chF").innerHTML);return!1},delNotify:function(){return window.confirm(document.getElementById("delN").innerHTML)},paste:function(a){var c=document.forms.post.sql;""!==a&&c&&this._insAtCaret(c,decodeURIComponent(a))},files:function(a){var c=document.createElement("input"),b=document.getElementById("fl");c.setAttribute("name","f[]");c.setAttribute("type","file");1===a?(b.insertBefore(c,null),b.appendChild(document.createElement("br"))):(a=b.getElementsByTagName("input"),b=b.getElementsByTagName("br"),0<a.length&&(a=a[a.length-1],a.parentNode.removeChild(a),b=b[b.length-1],b.parentNode.removeChild(b)))},edit:function(a,c){var b=c.parentNode,d=b.parentNode,e;if(void 0===this.id)this.id=d.lastChild.getAttribute("id").substring(1);this.id++;1===a?(e=b.cloneNode(!0),e.setAttribute("id","i"+this.id),e.getElementsByTagName("input").item(0).setAttribute("value",""),e.getElementsByTagName("td").item(0).innerHTML="+",d.insertBefore(e,b.nextSibling)):d.removeChild(b)},formatCode:function(a,c){var b=this._getCaretPosition(c),d,e="",f=0;if(13===this._getKey(a)&&!/opera mini|opera mobi/.test(window.navigator.userAgent.toLowerCase())){d=c.value.substring(0,b).split("\n");for(d=d[d.length-1].split("");f<d.length;f++)if(" "===d[f])e+=" ";else break;"{"===c.value.slice(b-1,b)&&(e+="    ");c.value=c.value.substring(0,b)+"\n"+e+c.value.substring(b,c.value.length);this._setCaretPosition(c,b+e.length+1);return!1}return!0}};