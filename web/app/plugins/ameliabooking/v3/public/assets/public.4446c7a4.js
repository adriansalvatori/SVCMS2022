import{e as p,b as w,_}from"./booking.27621e55.js";import{av as g,W as d,aw as f,r as c,p as o,ax as t,aY as A,aZ as v,a_ as L,au as b}from"./stepForm.92a42e7d.js";const P=g({loader:()=>_(()=>import(""+(window.__dynamic_handler__||function(e){return e})("./stepForm.92a42e7d.js")+"").then(function(e){return e.a$}),(window.__dynamic_preload__ || function(importer) { return importer; })(["assets/stepForm.92a42e7d.js","assets/stepForm.92fc808d.css"]))});typeof window.ameliaShortcodeData=="undefined"&&(window.ameliaShortcodeData=[{counter:null}]);const m=window.wpAmeliaUrls.wpAmeliaPluginURL+"v3/public/";window.__dynamic_handler__=function(e){return m+"assets/"+e};window.__dynamic_preload__=function(e){return e.map(a=>m+a)};window.ameliaShortcodeDataTriggered!==void 0&&window.ameliaShortcodeDataTriggered.forEach(e=>{let a=!1,l=setInterval(function(){let n=document.getElementById(e.trigger);!a&&n!==null&&typeof n!="undefined"&&(a=!0,clearInterval(l),n.onclick=function(){let r=setInterval(function(){let i=document.getElementsByClassName("amelia-skip-load-"+e.counter);if(i.length){clearInterval(r);for(let s=0;s<i.length;s++)i[s].classList.contains("amelia-v2-booking-"+e.counter+"-loaded")||u(e)}},1e3)})},1e3)});window.ameliaShortcodeData.forEach(e=>{u(e)});function u(e){const a=d(window.wpAmeliaSettings);let l=f({setup(){const n=d(window.wpAmeliaUrls),r=d(window.wpAmeliaLabels),i=c(window.localeLanguage[0]);o("settings",t(a)),o("baseUrls",t(n)),o("labels",t(r)),o("localLanguage",t(i)),o("shortcodeData",t(c(e)))}});a.googleTag.id&&l.use(A,{config:{id:window.wpAmeliaSettings.googleTag.id}}),a.facebookPixel.id&&(v(),L(window.wpAmeliaSettings.facebookPixel.id)),l.component("StepFormWrapper",P).use(b({modules:{entities:p,booking:w}})).mount(`#amelia-v2-booking${e.counter!==null?"-"+e.counter:""}`)}
