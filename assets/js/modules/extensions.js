window.performance.mark('(Nebula) Inside extensions.js (module)');

//These must be inside of this function to assure jQuery is defined
nebula.addExpressions = function(){
	//Custom CSS expression for a case-insensitive :contains(). Source: https://css-tricks.com/snippets/jquery/make-jquery-contains-case-insensitive/
	//Call it with :Contains() - Ex: ...find("*:Contains(" + jQuery('.something').val() + ")")... -or- use the nebula function: nebula.keywordSearch(container, parent, value);
	jQuery.expr.pseudos.Contains = function(element, index, match){
		return (element.textContent || element.innerText || '').toUpperCase().includes(match[3].toUpperCase());
	};

	//Custom expression for any element that can be focused. Source: https://github.com/selfthinker/dokuwiki_template_writr/blob/master/js/skip-link-focus-fix.js
	//Call it with :focusable() or .is(':focusable')
	jQuery.expr.pseudos.focusable = function(element, index, match){
		return jQuery(element).is(':input:enabled, a[href], area[href], object, [tabindex]') && !jQuery(element).is(':hidden');
	};
};

//Escape required characters from a provided string. https://github.com/kvz/locutus
nebula.preg_quote = function(string, delimiter){
	return String(string).replaceAll(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
};

//Add a method for .trimAll() that removes all duplicate whitespace in a string (including in the middle of the string)
String.prototype.trimAll = String.prototype.trimAll || function(){
	return this.replace(/\s+/g,' ').replace(/^\s+|\s+$/,'');
};

//Parse dates (equivalent of PHP strtotime function). https://github.com/kvz/locutus
nebula.strtotime = function(e, t){
	var a,n,r,s,u,i,o,w,c,d,D,g=!1;if(!e)return g;e=e.replace(/^\s+|\s+$/g,"").replace(/\s{2,}/g," ").replace(/[\t\r\n]/g,"").toLowerCase();var l=new RegExp(["^(\\d{1,4})","([\\-\\.\\/:])","(\\d{1,2})","([\\-\\.\\/:])","(\\d{1,4})","(?:\\s(\\d{1,2}):(\\d{2})?:?(\\d{2})?)?","(?:\\s([A-Z]+)?)?$"].join(""));if((n=e.match(l))&&n[2]===n[4])if(n[1]>1901)switch(n[2]){case"-":return n[3]>12||n[5]>31?g:new Date(n[1],parseInt(n[3],10)-1,n[5],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3;case".":return g;case"/":return n[3]>12||n[5]>31?g:new Date(n[1],parseInt(n[3],10)-1,n[5],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3}else if(n[5]>1901)switch(n[2]){case"-":case".":return n[3]>12||n[1]>31?g:new Date(n[5],parseInt(n[3],10)-1,n[1],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3;case"/":return n[1]>12||n[3]>31?g:new Date(n[5],parseInt(n[1],10)-1,n[3],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3}else switch(n[2]){case"-":return n[3]>12||n[5]>31||n[1]<70&&n[1]>38?g:(s=n[1]>=0&&n[1]<=38?+n[1]+2e3:n[1],new Date(s,parseInt(n[3],10)-1,n[5],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3);case".":return n[5]>=70?n[3]>12||n[1]>31?g:new Date(n[5],parseInt(n[3],10)-1,n[1],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3:n[5]<60&&!n[6]?n[1]>23||n[3]>59?g:(r=new Date,new Date(r.getFullYear(),r.getMonth(),r.getDate(),n[1]||0,n[3]||0,n[5]||0,n[9]||0)/1e3):g;case"/":return n[1]>12||n[3]>31||n[5]<70&&n[5]>38?g:(s=n[5]>=0&&n[5]<=38?+n[5]+2e3:n[5],new Date(s,parseInt(n[1],10)-1,n[3],n[6]||0,n[7]||0,n[8]||0,n[9]||0)/1e3);case":":return n[1]>23||n[3]>59||n[5]>59?g:(r=new Date,new Date(r.getFullYear(),r.getMonth(),r.getDate(),n[1]||0,n[3]||0,n[5]||0)/1e3)}if("now"===e)return null===t||isNaN(t)?(new Date).getTime()/1e3|0:0|t;if(!isNaN(a=Date.parse(e)))return a/1e3|0;if(l=new RegExp(["^([0-9]{4}-[0-9]{2}-[0-9]{2})","[ t]","([0-9]{2}:[0-9]{2}:[0-9]{2}(\\.[0-9]+)?)","([\\+-][0-9]{2}(:[0-9]{2})?|z)"].join("")),(n=e.match(l))&&("z"===n[4]?n[4]="Z":n[4].match(/^([+-][0-9]{2})$/)&&(n[4]=n[4]+":00"),!isNaN(a=Date.parse(n[1]+"T"+n[2]+n[4]))))return a/1e3|0;function f(e){var t,a,n,r,s=e.split(" "),w=s[0],c=s[1].substring(0,3),d=/\d+/.test(w),D="ago"===s[2],g=("last"===w?-1:1)*(D?-1:1);if(d&&(g*=parseInt(w,10)),o.hasOwnProperty(c)&&!s[1].match(/^mon(day|\.)?$/i))return u["set"+o[c]](u["get"+o[c]]()+g);if("wee"===c)return u.setDate(u.getDate()+7*g);if("next"===w||"last"===w)t=w,a=g,void 0!==(r=i[c])&&(0==(n=r-u.getDay())?n=7*a:n>0&&"last"===t?n-=7:n<0&&"next"===t&&(n+=7),u.setDate(u.getDate()+n));else if(!d)return!1;return!0}if(u=t?new Date(1e3*t):new Date,i={sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6},o={yea:"FullYear",mon:"Month",day:"Date",hou:"Hours",min:"Minutes",sec:"Seconds"},d="([+-]?\\d+\\s"+(c="(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)")+"|(last|next)\\s"+c+")(\\sago)?",!(n=e.match(new RegExp(d,"gi"))))return g;for(D=0,w=n.length;D<w;D++)if(!f(n[D]))return g;return u.getTime()/1e3;
};