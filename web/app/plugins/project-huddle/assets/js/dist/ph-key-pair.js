!function(e,n){"use strict";var t=e("#key-pairs-section"),a=t.find(".create-key-pair"),o=a.find(".input"),i=a.find(".button"),r=t.find(".key-pairs-list-table-wrapper"),c=r.find("tbody"),d=c.find(".no-items"),p=e("#revoke-all-key-pairs"),l=wp.template("new-key-pair"),s=wp.template("key-pair-row"),f=wp.template("new-token-key-pair");function u(e,n){var t=document.createElement("a"),a="text/json;charset=utf-8,"+encodeURIComponent(JSON.stringify(n));t.href="data:"+a,t.download=e,document.body.appendChild(t),t.click(),t.remove()}i.click((function(t){var p=o.val();t.preventDefault(),0!==p.length?(o.prop("disabled",!0),i.prop("disabled",!0),e.ajax({url:n.root+"/"+n.user_id,method:"POST",beforeSend:function(e){e.setRequestHeader("X-WP-Nonce",n.nonce)},data:{name:p}}).done((function(e){o.prop("disabled",!1).val(""),i.prop("disabled",!1),a.after(l({name:p,api_key:e.row.api_key,api_secret:e.api_secret})),c.prepend(s(e.row)),r.show(),d.remove()}))):o.focus()})),c.on("click",".delete",(function(t){var a=e(t.target).closest("tr"),o=a.data("api_key"),i=a.data("name");t.preventDefault(),confirm(n.text.confirm_one.replace("%s",i))&&e.ajax({url:n.root+"/"+n.user_id+"/"+o+"/revoke",method:"DELETE",beforeSend:function(e){e.setRequestHeader("X-WP-Nonce",n.nonce)}}).done((function(e){e&&(0===a.siblings().length&&r.hide(),a.remove())}))})),c.on("click",".token .button",(function(n){var a=e(n.target).closest("tr"),o=a.data("api_key"),i=a.data("name");n.preventDefault(),t.after(f({name:i,api_key:o}))})),e(document).on("click",".key-pair-token",(function(a){var o=e(a.target).closest(".new-key-pair"),i=e('input[name="new_token_api_secret"]'),r=o.data("api_key"),c=i.val(),d=o.data("name");a.preventDefault(),0!==c.length?e.ajax({url:n.token,method:"POST",data:{api_key:r,api_secret:c}}).done((function(n){e(".new-key-pair.notification-dialog-wrap").remove(),t.after(f({name:d,api_key:r,access_token:n.access_token,refresh_token:n.refresh_token})),e(document).on("click",".key-pair-token-download",(function(e){e.preventDefault(),u("token.json",n)}))})).fail((function(n){e(".new-key-pair.notification-dialog-wrap").remove(),t.after(f({name:d,api_key:r,message:n.responseJSON.message}))})):i.focus()})),p.on("click",(function(a){a.preventDefault(),confirm(n.text.confirm_all)&&e.ajax({url:n.root+"/"+n.user_id+"/revoke-all",method:"DELETE",beforeSend:function(e){e.setRequestHeader("X-WP-Nonce",n.nonce)}}).done((function(e){parseInt(e,10)>0&&(c.children().remove(),t.children(".new-key-pair").remove(),r.hide())}))})),e(document).on("click",".input-select",(function(){e(this).select()})),e(document).on("click",".key-pair-modal-dismiss",(function(n){n.preventDefault(),e(".new-key-pair.notification-dialog-wrap").remove()})),e(document).on("click",".key-pair-download",(function(n){n.preventDefault(),u("key-pair.json",{api_key:e(this).data("key"),api_secret:e(this).data("secret")})})),0===c.children("tr").not(d).length&&r.hide()}(jQuery,keyPair);
