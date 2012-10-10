/*
YUI 3.7.2 (build 5639)
Copyright 2012 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/
YUI.add("console-filters",function(e,t){function b(){b.superclass.constructor.apply(this,arguments)}var n=e.ClassNameManager.getClassName,r="console",i="filters",s="filter",o="category",u="source",a="category.",f="source.",l="host",c="checked",h="defaultVisibility",p=".",d="",v=p+e.Console.CHROME_CLASSES.console_bd_class,m=p+e.Console.CHROME_CLASSES.console_ft_class,g="input[type=checkbox].",y=e.Lang.isString;e.namespace("Plugin").ConsoleFilters=e.extend(b,e.Plugin.Base,{_entries:null,_cacheLimit:Number.POSITIVE_INFINITY,_categories:null,_sources:null,initializer:function(){this._entries=[],this.get(l).on("entry",this._onEntry,this),this.doAfter("renderUI",this.renderUI),this.doAfter("syncUI",this.syncUI),this.doAfter("bindUI",this.bindUI),this.doAfter("clearConsole",this._afterClearConsole),this.get(l).get("rendered")&&(this.renderUI(),this.syncUI(),this.bindUI()),this.after("cacheLimitChange",this._afterCacheLimitChange)},destructor:function(){this._entries=[],this._categories&&this._categories.remove(),this._sources&&this._sources.remove()},renderUI:function(){var t=this.get(l).get("contentBox").one(m),n;t&&(n=e.Lang.sub(b.CATEGORIES_TEMPLATE,b.CHROME_CLASSES),this._categories=t.appendChild(e.Node.create(n)),n=e.Lang.sub(b.SOURCES_TEMPLATE,b.CHROME_CLASSES),this._sources=t.appendChild(e.Node.create(n)))},bindUI:function(){this._categories.on("click",e.bind(this._onCategoryCheckboxClick,this)),this._sources.on("click",e.bind(this._onSourceCheckboxClick,this)),this.after("categoryChange",this._afterCategoryChange),this.after("sourceChange",this._afterSourceChange)},syncUI:function(){e.each(this.get(o),function(e,t){this._uiSetCheckbox(o,t,e)},this),e.each(this.get(u),function(e,t){this._uiSetCheckbox(u,t,e)},this),this.refreshConsole()},_onEntry:function(e){this._entries.push(e.message);var t=a+e.message.category,n=f+e.message.source,r=this.get(t),i=this.get(n),s=this._entries.length-this._cacheLimit,o;s>0&&this._entries.splice(0,s),r===undefined&&(o=this.get(h),this.set(t,o),r=o),i===undefined&&(o=this.get(h),this.set(n,o),i=o),(!r||!i)&&e.preventDefault()},_afterClearConsole:function(){this._entries=[]},_afterCategoryChange:function(e){var t=e.subAttrName.replace(/category\./,d),n=e.prevVal,r=e.newVal;if(!t||n[t]!==undefined)this.refreshConsole(),this._filterBuffer();t&&!e.fromUI&&this._uiSetCheckbox(o,t,r[t])},_afterSourceChange:function(e){var t=e.subAttrName.replace(/source\./,d),n=e.prevVal,r=e.newVal;if(!t||n[t]!==undefined)this.refreshConsole(),this._filterBuffer();t&&!e.fromUI&&this._uiSetCheckbox(u,t,r[t])},_filterBuffer:function(){var e=this.get(o),t=this.get(u),n=this.get(l).buffer,r=null,i;for(i=n.length-1;i>=0;--i)!e[n[i].category]||!t[n[i].source]?r=r||i:r&&(n.splice(i,r-i),r=null);r&&n.splice(0,r+1)},_afterCacheLimitChange:function(e){if(isFinite(e.newVal)){var t=this._entries.length-e.newVal;t>0&&this._entries.splice(0,t)}},refreshConsole:function(){var e=this._entries,t=this.get(l),n=t.get("contentBox").one(v),r=t.get("consoleLimit"),i=this.get(o),s=this.get(u),a=[],f,c;if(n){t._cancelPrintLoop();for(f=e.length-1;f>=0&&r>=0;--f)c=e[f],i[c.category]&&s[c.source]&&(a.unshift(c),--r);n.setHTML(d),t.buffer=a,t.printBuffer()}},_uiSetCheckbox:function(e,t,i){if(e&&t){var u=e===o?this._categories:this._sources,a=g+n(r,s,t),f=u.one(a),h;f||(h=this.get(l),this._createCheckbox(u,t),f=u.one(a),h._uiSetHeight(h.get("height"))),f.set(c,i)}},_onCategoryCheckboxClick:function(e){var t=e.target,n;t.hasClass(b.CHROME_CLASSES.filter)&&(n=t.get("value"),n&&n in this.get(o)&&this.set(a+n,t.get(c),{fromUI:!0}))},_onSourceCheckboxClick:function(e){var t=e.target,n;t.hasClass(b.CHROME_CLASSES.filter)&&(n=t.get("value"),n&&n in this.get(u)&&this.set(f+n,t.get(c),{fromUI:!0}))},hideCategory:function(t,n){y(n)?e.Array.each(arguments,this.hideCategory,this):this.set(a+t,!1)},showCategory:function(t,n){y(n)?e.Array.each(arguments,this.showCategory,this):this.set(a+t,!0)},hideSource:function(t,n){y(n)?e.Array.each(arguments,this.hideSource,this):this.set(f+t,!1)},showSource:function(t,n){y(n)?e.Array.each(arguments,this.showSource,this):this.set(f+t,!0)},_createCheckbox:function(t,i){var o=e.merge(b.CHROME_CLASSES,{filter_name:i,filter_class:n(r,s,i)}),u=e.Node.create(e.Lang.sub(b.FILTER_TEMPLATE,o));t.appendChild(u)},_validateCategory:function(t,n){return e.Lang.isObject(n,!0)&&t.split(/\./).length<3},_validateSource:function(t,n){return e.Lang.isObject(n,!0)&&t.split(/\./).length<3},_setCacheLimit:function(t){return e.Lang.isNumber(t)?(this._cacheLimit=t,t):e.Attribute.INVALID_VALUE}},{NAME:"consoleFilters",NS:s,CATEGORIES_TEMPLATE:'<div class="{categories}"></div>',SOURCES_TEMPLATE:'<div class="{sources}"></div>',FILTER_TEMPLATE:'<label class="{filter_label}"><input type="checkbox" value="{filter_name}" class="{filter} {filter_class}"> {filter_name}</label>&#8201;',CHROME_CLASSES:{categories:n(r,i,"categories"),sources:n(r,i,"sources"),category:n(r,s,o),source:n(r,s,u),filter:n(r,s),filter_label:n(r,s,"label")},ATTRS:{defaultVisibility:{value:!0,validator:e.Lang.isBoolean},category:{value:{},validator:function(e,t){return this._validateCategory(t,e)}},source:{value:{},validator:function(e,t){return this._validateSource(t,e)}},cacheLimit:{value:Number.POSITIVE_INFINITY,setter:function(e){return this._setCacheLimit(e)}}}})},"3.7.2",{requires:["plugin","console"],skinnable:!0});
