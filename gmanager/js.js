function number(e){var key=(e.charCode===undefined?e.keyCode:e.charCode);if(e.ctrlKey||e.altKey||key<32){return true;}key=String.fromCharCode(key);return /[\d]/.test(key);}
function check(form,name,checked){for(var i=0;i<form[name].length;i++){form[name][i].checked=checked;}}
function checkForm(form,name){if(form[name]===undefined){return false;}else if(form[name] instanceof HTMLInputElement){if(!form[name].checked){alert(document.getElementById("chF").innerHTML);}return form[name].checked;}for(var i=0;i<form[name].length;i++){if(form[name][i].checked){return true;}}alert(document.getElementById("chF").innerHTML);return false;}
function delNotify(){return confirm(document.getElementById("delN").innerHTML);}
function paste(ptn){o=document.forms["post"]["sql"];if(ptn!=""&&o)insAtCaret(o,decodeURIComponent(ptn));}
function insAtCaret(o,s){o.focus();if(document.selection!==undefined){r=document.selection.createRange();if(r.parentElement()!=o){return;}r.text=s;r.select();}else if(o.selectionStart!==undefined){st=o.selectionStart;o.value=o.value.substr(0,st)+s+o.value.substr(o.selectionEnd,o.value.length);st+=s.length;o.setSelectionRange(st,st);}else{o.value+=s;}o.focus();}
function files(type){var f=document.createElement("input");f.setAttribute("name","f[]");f.setAttribute("type","file");var fl=document.getElementById("fl");if(type==1){fl.insertBefore(f,null);fl.appendChild(document.createElement("br"));}else{var input=fl.getElementsByTagName("input");var br=fl.getElementsByTagName("br");if(input.length>0){var el=input[input.length-1];el.parentNode.removeChild(el);var el2=br[br.length-1];el2.parentNode.removeChild(el2);}}}
function edit(type,n){var tr=n.parentNode;var table=tr.parentNode;if(this.id===undefined){this.id=table.lastChild.getAttribute("id").substring(1);}this.id++;if(type==1){var f=tr.cloneNode(true);f.setAttribute("id","i"+this.id);var input=f.getElementsByTagName("input").item(0);input.setAttribute("value","");var num=f.getElementsByTagName("td").item(0);num.innerHTML="+";table.insertBefore(f,tr.nextSibling);}else{table.removeChild(tr);}}