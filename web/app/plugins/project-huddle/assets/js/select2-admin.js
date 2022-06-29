(function($) {
  $(document).ready(function($) {
    var endpoint_base = wpApiSettings.root;

    var select2 = $(".ph-select2");

    select2.each(function() {
      var $this = $(this);

      $this.select2({
        multiple: true,
        tags: false,
        language: {
          noResults: function() {
            return PH.translations.no_results;
          },
          inputTooShort: function() {
            return PH.translations.input_too_short;
          }
        },
        placeholder: PH.translations.subscribe_people,

        templateResult: formatAvatar,
        templateSelection: formatAvatar,

        ajax: {
          url: endpoint_base + "projecthuddle/v2/users",
          dataType: "json",
          type: "get",
          cache: false,

          beforeSend: function(xhr) {
            xhr.setRequestHeader("X-WP-Nonce", wpApiSettings.nonce);
          },

          data: function(params) {
            return {
              order: "asc",
              search: params.term,
              page: params.page || 1
            };
          },

          processResults: function(data, params) {

            params.page = params.page || 1;

            var results = [];
            _.each(data.items, function(value, key, list) {
              var obj = {
                id: value.id || value.data.id,
                avatar: value.avatar_urls[24] || "",
                text: value.name,
                role: value.user_role
              };
              results.push(obj);
            });

            return {
              results: results,
              pagination: {
                more: params.page * 10 < data.totalPages
              }
            };
          },

          // Custom pagination
          transport: function(params, success, failure) {
            var $request = $.ajax(params);

            var callback = success;
            success = function(data, textStatus, jqXHR) {
              callback(
                {
                  items: data,
                  total: jqXHR.getResponseHeader("X-WP-Total"),
                  totalPages: jqXHR.getResponseHeader("X-WP-TotalPages")
                },
                textStatus,
                jqXHR
              );
            };

            $request.then(success);
            $request.fail(failure);

            return $request;
          }
        }
      });
    });

    // constant placeholder
    $(".select2-search__field").attr(
      "placeholder",
      PH.translations.subscribe_placeholder
    );
    select2.on(
      "select2:open select2:opening select2:select select2:selecting select2:unselect select2:unselecting select2:close select2:closing  change",
      function() {
        setTimeout(function() {
          $(".select2-search__field").attr(
            "placeholder",
            PH.translations.subscribe_placeholder
          );
        }, 0);
      }
    );

    // Add avatar to select
    function formatAvatar(user) {
      var avatar = user.avatar;
      var role = user.role;
      var $user = '';

      if (user.text.length == 0) {
        return false;
      }
      if (
        !avatar &&
        user.element &&
        user.element.dataset &&
        user.element.dataset.avatar
      ) {
        avatar = user.element.dataset.avatar;
      }

      if (
        !role &&
        user.element &&
        user.element.dataset &&
        user.element.dataset.role
      ) {
        role = user.element.dataset.role;
      }

      var role_text = ( "Searching…" !== user.text ) ? '  <i style="font-size: 0.9em;">(' + role + ')</i>' : '';

      if (!avatar) {
        $user = jQuery(
          '<span class="ph-select-username">' +
            user.text + role_text +
            "</span>"
        );
        return $user;
      }
      $user = jQuery(
        '<span class="ph-select-avatar"><img src="' +
          avatar +
          '" /></span><span><span class="ph-select-username">' +
          user.text + role_text +
          "</span></span>"
      );
      return $user;
    }
  });
})(jQuery);
