!function(t){var e={};function i(n){if(e[n])return e[n].exports;var s=e[n]={i:n,l:!1,exports:{}};return t[n].call(s.exports,s,s.exports,i),s.l=!0,s.exports}i.m=t,i.c=e,i.d=function(t,e,n){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var s in t)i.d(n,s,function(e){return t[e]}.bind(null,s));return n},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="",i(i.s=14)}({0:function(t,e,i){(function(e){var i="Expected a function",n="__lodash_hash_undefined__",s=1/0,r="[object Function]",a="[object GeneratorFunction]",o="[object Symbol]",c=/\.|\[(?:[^[\]]*|(["'])(?:(?!\1)[^\\]|\\.)*?\1)\]/,h=/^\w*$/,d=/^\./,l=/[^.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|$))/g,u=/\\(\\)?/g,p=/^\[object .+?Constructor\]$/,m="object"==typeof e&&e&&e.Object===Object&&e,f="object"==typeof self&&self&&self.Object===Object&&self,_=m||f||Function("return this")();var v,g=Array.prototype,y=Function.prototype,$=Object.prototype,b=_["__core-js_shared__"],j=(v=/[^.]+$/.exec(b&&b.keys&&b.keys.IE_PROTO||""))?"Symbol(src)_1."+v:"",C=y.toString,w=$.hasOwnProperty,x=$.toString,O=RegExp("^"+C.call(w).replace(/[\\^$.*+?()[\]{}|]/g,"\\$&").replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g,"$1.*?")+"$"),S=_.Symbol,A=g.splice,T=R(_,"Map"),k=R(Object,"create"),E=S?S.prototype:void 0,D=E?E.toString:void 0;function F(t){var e=-1,i=t?t.length:0;for(this.clear();++e<i;){var n=t[e];this.set(n[0],n[1])}}function L(t){var e=-1,i=t?t.length:0;for(this.clear();++e<i;){var n=t[e];this.set(n[0],n[1])}}function M(t){var e=-1,i=t?t.length:0;for(this.clear();++e<i;){var n=t[e];this.set(n[0],n[1])}}function P(t,e){for(var i,n,s=t.length;s--;)if((i=t[s][0])===(n=e)||i!=i&&n!=n)return s;return-1}function U(t,e){for(var i,n=0,s=(e=function(t,e){if(V(t))return!1;var i=typeof t;if("number"==i||"symbol"==i||"boolean"==i||null==t||H(t))return!0;return h.test(t)||!c.test(t)||null!=e&&t in Object(e)}(e,t)?[e]:V(i=e)?i:z(i)).length;null!=t&&n<s;)t=t[B(e[n++])];return n&&n==s?t:void 0}function I(t){return!(!X(t)||(e=t,j&&j in e))&&(function(t){var e=X(t)?x.call(t):"";return e==r||e==a}(t)||function(t){var e=!1;if(null!=t&&"function"!=typeof t.toString)try{e=!!(t+"")}catch(t){}return e}(t)?O:p).test(function(t){if(null!=t){try{return C.call(t)}catch(t){}try{return t+""}catch(t){}}return""}(t));var e}function N(t,e){var i,n,s=t.__data__;return("string"==(n=typeof(i=e))||"number"==n||"symbol"==n||"boolean"==n?"__proto__"!==i:null===i)?s["string"==typeof e?"string":"hash"]:s.map}function R(t,e){var i=function(t,e){return null==t?void 0:t[e]}(t,e);return I(i)?i:void 0}F.prototype.clear=function(){this.__data__=k?k(null):{}},F.prototype.delete=function(t){return this.has(t)&&delete this.__data__[t]},F.prototype.get=function(t){var e=this.__data__;if(k){var i=e[t];return i===n?void 0:i}return w.call(e,t)?e[t]:void 0},F.prototype.has=function(t){var e=this.__data__;return k?void 0!==e[t]:w.call(e,t)},F.prototype.set=function(t,e){return this.__data__[t]=k&&void 0===e?n:e,this},L.prototype.clear=function(){this.__data__=[]},L.prototype.delete=function(t){var e=this.__data__,i=P(e,t);return!(i<0||(i==e.length-1?e.pop():A.call(e,i,1),0))},L.prototype.get=function(t){var e=this.__data__,i=P(e,t);return i<0?void 0:e[i][1]},L.prototype.has=function(t){return P(this.__data__,t)>-1},L.prototype.set=function(t,e){var i=this.__data__,n=P(i,t);return n<0?i.push([t,e]):i[n][1]=e,this},M.prototype.clear=function(){this.__data__={hash:new F,map:new(T||L),string:new F}},M.prototype.delete=function(t){return N(this,t).delete(t)},M.prototype.get=function(t){return N(this,t).get(t)},M.prototype.has=function(t){return N(this,t).has(t)},M.prototype.set=function(t,e){return N(this,t).set(t,e),this};var z=G(function(t){var e;t=null==(e=t)?"":function(t){if("string"==typeof t)return t;if(H(t))return D?D.call(t):"";var e=t+"";return"0"==e&&1/t==-s?"-0":e}(e);var i=[];return d.test(t)&&i.push(""),t.replace(l,function(t,e,n,s){i.push(n?s.replace(u,"$1"):e||t)}),i});function B(t){if("string"==typeof t||H(t))return t;var e=t+"";return"0"==e&&1/t==-s?"-0":e}function G(t,e){if("function"!=typeof t||e&&"function"!=typeof e)throw new TypeError(i);var n=function(){var i=arguments,s=e?e.apply(this,i):i[0],r=n.cache;if(r.has(s))return r.get(s);var a=t.apply(this,i);return n.cache=r.set(s,a),a};return n.cache=new(G.Cache||M),n}G.Cache=M;var V=Array.isArray;function X(t){var e=typeof t;return!!t&&("object"==e||"function"==e)}function H(t){return"symbol"==typeof t||function(t){return!!t&&"object"==typeof t}(t)&&x.call(t)==o}t.exports=function(t,e,i){var n=null==t?void 0:U(t,e);return void 0===n?i:n}}).call(this,i(2))},1:function(t,e,i){"use strict";function n(t,e,i,n,s,r,a,o){var c,h="function"==typeof t?t.options:t;if(e&&(h.render=e,h.staticRenderFns=i,h._compiled=!0),n&&(h.functional=!0),r&&(h._scopeId="data-v-"+r),a?(c=function(t){(t=t||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext)||"undefined"==typeof __VUE_SSR_CONTEXT__||(t=__VUE_SSR_CONTEXT__),s&&s.call(this,t),t&&t._registeredComponents&&t._registeredComponents.add(a)},h._ssrRegister=c):s&&(c=o?function(){s.call(this,this.$root.$options.shadowRoot)}:s),c)if(h.functional){h._injectStyles=c;var d=h.render;h.render=function(t,e){return c.call(e),d(t,e)}}else{var l=h.beforeCreate;h.beforeCreate=l?[].concat(l,c):[c]}return{exports:t,options:h}}i.d(e,"a",function(){return n})},13:function(t,e,i){"use strict";
/*! file-extension v4.0.5 | (c) silverwind | BSD license */t.exports=function(t,e){if(e||(e={}),!t)return"";var i=(/[^./\\]*$/.exec(t)||[""])[0];return e.preserveCase?i:i.toLowerCase()}},14:function(t,e,i){t.exports=i(31)},2:function(t,e){var i;i=function(){return this}();try{i=i||new Function("return this")()}catch(t){"object"==typeof window&&(i=window)}t.exports=i},31:function(t,e,i){"use strict";i.r(e);var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"ph-file-attachment-icon ph-cursor-pointer"},[i("div",{staticClass:"ph-tooltip-wrap ph-add-file",on:{click:function(e){return e.preventDefault(),t.addFile(e)}}},[i("svg",{staticClass:"feather feather-paperclip",staticStyle:{fill:"none"},attrs:{xmlns:"http://www.w3.org/2000/svg",width:"16",height:"16",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2","stroke-linecap":"round","stroke-linejoin":"round"}},[i("path",{attrs:{d:"M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"}})]),t._v(" "),i("div",{staticClass:"ph-tooltip"},[t._v(t._s(t.__("Attach A File","project-huddle")))])]),t._v(" "),i("input",{ref:"file",staticClass:"ph-file-input",staticStyle:{display:"none"},attrs:{type:"file",accept:t.accepts},on:{change:t.uploadImage}})])};n._withStripped=!0;var s=i(0),r=i.n(s),a={computed:{accepts:()=>PHF_Settings.types},methods:{addFile(){r()(this,"me.id")?this.$refs.file.click():ph.store.commit("ui/SET_DIALOG",{name:"register",success:this.addFile})},uploadImage(t){this.$refs.file.files&&(this.$emit("upload",this.$refs.file.files),this.$refs.file.value="")}}},o=i(1),c=Object(o.a)(a,n,[],!1,null,null,null);c.options.__file="assets/src/front-end/js/components/UploadIcon.vue";var h=c.exports;const{extendComponent:d}=ph.components;d("shared.editor",{mounted(){this.insertIntoSlot("footer-right-after",h,{props:{thread:this.thread},on:{upload:t=>{this.$emit("upload",t)}}})}});var l=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.thread&&t.thread.attachment_ids.length?i("div",{staticClass:"ph-attachment-container",staticStyle:{"margin-bottom":"0"}},t._l(t.thread.attachment_ids,function(e){return i("ThreadAttachment",{key:e,attrs:{id:e,thread:t.thread}})}),1):t._e()};l._withStripped=!0;var u=function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"ph-comment__attachment"},[this.loaded?[e("Attachment",{attrs:{media:this.media,canDelete:this.canDelete},on:{delete:this.confirmTrash}})]:[e("AttachmentLoading",{attrs:{progress:this.progress}})]],2)};u._withStripped=!0;var p=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"ph-file-attachment-thumbnail"},[i("div",{staticClass:"thumb-icon",style:[t.url?{"background-image":"url("+t.url+")"}:{}]},[t.progress<100?[t._m(0),t._v(" "),i("div",{staticClass:"ph-upload-progress"},[i("div",{staticClass:"ph-upload-progress-indicator",style:{width:t.progress+"%"}})])]:[t.canDelete?i("div",{staticClass:"ph-close-icon ph-tooltip-wrap",on:{click:function(e){return e.preventDefault(),t.$emit("delete")}}},[i("svg",{attrs:{viewBox:"0 0 512 512",id:"ion-android-close",width:"9",height:"9"}},[i("path",{attrs:{d:"M405 136.798L375.202 107 256 226.202 136.798 107 107 136.798 226.202 256 107 375.202 136.798 405 256 285.798 375.202 405 405 375.202 285.798 256z"}})]),t._v(" "),i("div",{staticClass:"ph-tooltip"},[t._v(t._s(t.__("Delete","project-huddle")))])]):t._e(),t._v(" "),t.url?t._e():i("div",{staticClass:"ph-generic-attachment-icon"},[t._v(t._s(t.extension))]),t._v(" "),i("a",{staticClass:"ph-download",attrs:{href:t.sourceUrl,download:t.title,target:"_blank"}},[i("svg",{attrs:{viewBox:"0 0 512 512",id:"ion-android-download",width:"18",height:"18",fill:"#fff"}},[i("path",{attrs:{d:"M403.002 217.001C388.998 148.002 328.998 96 256 96c-57.998 0-107.998 32.998-132.998 81.001C63.002 183.002 16 233.998 16 296c0 65.996 53.999 120 120 120h260c55 0 100-45 100-100 0-52.998-40.996-96.001-92.998-98.999zM224 268v-76h64v76h68L256 368 156 268h68z"}})])])]],2),t._v(" "),i("div",{staticClass:"ph-attachment-title",attrs:{"data-text":t.title||!1}},[t._v(t._s(t.title))])])};p._withStripped=!0;var m=i(13),f=i.n(m),v={props:{id:Number,media:Object,progress:Number,canDelete:Boolean},computed:{sourceUrl(){return r()(this,"media.source_url")},url(){return r()(this,"media.media_details.sizes.thumbnail.source_url")},extension(){return this.sourceUrl&&f()(this.sourceUrl)},title(){return r()(this,"media.title.rendered")}}},g=Object(o.a)(v,p,[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"ph-loading-image light"},[e("div",{staticClass:"ph-loading-image-dots"})])}],!1,null,null,null);g.options.__file="assets/src/front-end/js/components/Attachment.vue";var y=g.exports,$=function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"ph-file-attachment-thumbnail"},[e("div",{staticClass:"thumb-icon"},[this._m(0),this._v(" "),e("div",{staticClass:"ph-upload-progress"},[e("div",{staticClass:"ph-upload-progress-indicator",style:{width:this.progress+"%"}})])]),this._v(" "),e("div",{staticClass:"ph-attachment-title"})])};$._withStripped=!0;var b={props:{progress:Number}},j=Object(o.a)(b,$,[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"ph-loading-image light"},[e("div",{staticClass:"ph-loading-image-dots"})])}],!1,null,null,null);j.options.__file="assets/src/front-end/js/components/AttachmentLoading.vue";var C=j.exports,w={props:{id:Number|String,thread:Object},components:{Attachment:y,AttachmentLoading:C},data:()=>({progress:100}),mounted(){console.log(this.thread),this.loaded||this.upload()},computed:{media(){return this.$store.getters["entities/media/find"](this.id)},loaded(){return this.media&&_.isNumber(this.media.id)},canDelete(){return!!this.loaded&&(r()(this,"$store.state.entities.users.me.id")==this.media.author||!!r()(this,"$store.state.entities.users.me.capabilities.moderate_comments"))}},methods:{confirmTrash(){this.$store.commit("ui/SET_DIALOG",{name:"dialog",title:this.__("Permanently delete this upload?","project-huddle"),message:`<p>${this.__("Are you sure you want to delete this file? This is permanent.","project-huddle")}</p>`,success:this.trash})},trash(){let t=r()(this.media,"_links.self[0].href");this.remove(),t&&this.$http.delete(t,{params:{force:!0}}).then(([t])=>{this.$notification({text:this.__("Attachment deleted.","project-huddle"),duration:5e3})}).catch(t=>{this.$store.dispatch("entities/insert",{entity:"media",data:attachment}),this.$error(t)})},upload(){if(this.thread.$update({disabled:!0}),this.progress=0,!this.media||!this.media.file)return;var t=new FormData;let e={file:this.media.file};this.media.post&&(e.post=this.media.post),_.each(e,function(e,i){t.append(i,e)}),this.$http.post("/media",{data:t,options:{async:!0,cache:!1,contentType:!1,processData:!1,xhr:()=>{var t=jQuery.ajaxSettings.xhr();return t.upload.onprogress=(t=>{t.lengthComputable&&(this.progress=t.loaded/t.total*100)}),t.upload.onload=(()=>{this.progress=100}),t}}}).then(([t])=>{this.progress=100,this.$store.dispatch("entities/insertOrUpdate",{entity:"media",data:t}).then(()=>{this.replace(t.id)})}).catch(t=>{this.$error(t),this.media.$delete()}).finally(()=>{this.thread.$update({disabled:!1})})},replace(t){let e=_.without(this.thread.attachment_ids,this.media.id);e.push(t),this.thread.$update({attachment_ids:e}).then(()=>{this.media.$delete()})},remove(){let t=_.without(this.thread.attachment_ids,this.media.id);this.thread.$update({attachment_ids:t}).then(()=>{this.media.$delete()})}}},x=Object(o.a)(w,u,[],!1,null,null,null);x.options.__file="assets/src/front-end/js/components/ThreadAttachment.vue";var O={name:"threadAttachment",components:{ThreadAttachment:x.exports},props:{id:Number|String},computed:{thread(){var t,e,i,n;let s=(null===(t=this.$store)||void 0===t?void 0:null===(e=t.getters)||void 0===e?void 0:e["entities/mockup-threads/find"])||(null===(i=this.$store)||void 0===i?void 0:null===(n=i.getters)||void 0===n?void 0:n["entities/website-threads/find"]);return s?s(this.id):{}}}},S=Object(o.a)(O,l,[],!1,null,null,null);S.options.__file="assets/src/front-end/js/components/ThreadAttachments.vue";var A=S.exports;const{extendComponent:T}=ph.components;T("thread.body",{data:()=>({attachments:[]}),mounted(){this.$refs.editor.$on("upload",this.uploadFile),this.insertComponent({ref:"form",component:A,data:{propsData:{id:this.thread.id}},key:"test"}),ph.hooks.addFilter("ph_new_comment_data","ph.file-uploads",(t,e)=>(t.attachment_ids=this.thread.attachment_ids,this.thread.$update({attachment_ids:[]}),t))},methods:{async uploadFile(t){let{media:e}=await this.$store.dispatch("entities/insert",{entity:"media",data:{post:this.thread.id||0,file:t[0]}});this.thread.$update({attachment_ids:[...this.thread.attachment_ids,..._.pluck(e,"id")]})}}});var k=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"ph-comment-attachment-container"},t._l(t.attachment_ids,function(e){return i("CommentAttachment",{key:e,staticClass:"ph-comment__attachment",attrs:{id:e,comment:t.comment}})}),1)};k._withStripped=!0;var E=function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"ph-comment__attachment"},[this.media?e("Attachment",{attrs:{media:this.media,canDelete:this.canDelete},on:{delete:this.confirmTrash}}):e("AttachmentLoading")],1)};E._withStripped=!0;var D={props:{id:Number,comment:Object},components:{Attachment:y,AttachmentLoading:C},computed:{media(){return this.$store.getters["entities/media/find"](this.id)},isAuthor(){return r()(this,"$store.state.entities.users.me.id")==r()(this,"media.author")},canModerate(){return r()(this,"$store.state.entities.users.me.capabilities.moderate_comments")},canDelete(){return this.isAuthor||this.canModerate}},methods:{confirmTrash(){this.$store.commit("ui/SET_DIALOG",{name:"dialog",title:this.__("Permanently delete this upload?","project-huddle"),message:`<p>${this.__("Are you sure you want to delete this file? This is permanent.","project-huddle")}</p>`,success:this.trash})},trash(){this.comment.$dispatch("update",{where:this.comment.id,data:t=>{this.$delete(t.attachment_ids,_.indexOf(t.attachment_ids,this.media.id))}}),this.$parent.$forceUpdate(),this.comment.$dispatch("patch",{comment:this.comment,data:{attachment_ids:0===this.comment.attachment_ids.length?"":this.comment.attachment_ids}});let t=this.media,e=r()(this.media,"_links.self[0].href");e&&this.$http.delete(e,{params:{force:!0}}).then(([t])=>{this.$notification({text:this.__("Attachment deleted.","project-huddle"),duration:5e3})}).catch(e=>{this.$store.dispatch("entities/insert",{entity:"media",data:t}),this.$error(e)})}}},F=Object(o.a)(D,E,[],!1,null,null,null);F.options.__file="assets/src/front-end/js/components/CommentAttachment.vue";var L={components:{CommentAttachment:F.exports},props:{comment:Object},computed:{attachment_ids(){return this.comment.attachment_ids}}},M=Object(o.a)(L,k,[],!1,null,null,null);M.options.__file="assets/src/front-end/js/components/CommentAttachments.vue";var P=M.exports;const{extendComponent:U}=ph.components;U("shared.comment",{mounted(){this.insertComponent({ref:"content",component:P,data:{propsData:{comment:this.comment}},key:`attachments-${this.comment.id}`}),this.comment.attachment_ids.length&&this.$http.get("/media/",{params:{include:this.comment.attachment_ids}}).then(([t])=>{_.each(t,t=>{t.post=0}),this.$store.dispatch("entities/insertOrUpdate",{entity:"media",data:t})})}})}});