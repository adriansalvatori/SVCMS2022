<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

?>

<script>
  if (typeof hasAmeliaEntitiesApiCall === 'undefined' && '<?php echo esc_js($params['trigger']); ?>' === '') {
    var hasAmeliaEntitiesApiCall = true;
  }
  var ameliaShortcodeData = (typeof ameliaShortcodeData === 'undefined') ? [] : ameliaShortcodeData;
  ameliaShortcodeData.push(
    {
      'hasApiCall': (typeof hasAmeliaEntitiesApiCall !== 'undefined') && hasAmeliaEntitiesApiCall,
      'trigger': '<?php echo esc_js($params['trigger']); ?>',
      'show': '<?php echo esc_js($params['show']); ?>',
      'counter': '<?php echo esc_js($params['counter']); ?>',
      'category': '<?php echo esc_js($params['category']); ?>',
      'service': '<?php echo esc_js($params['service']); ?>',
      'employee': '<?php echo esc_js($params['employee']); ?>',
      'location': '<?php echo esc_js($params['location']); ?>'
    }
  );
  var ameliaShortcodeDataTriggered = (typeof ameliaShortcodeDataTriggered === 'undefined') ? [] : ameliaShortcodeDataTriggered;
  if (ameliaShortcodeData[ameliaShortcodeData.length - 1].trigger !== '') {
    ameliaShortcodeDataTriggered.push(ameliaShortcodeData.pop());
  }
  if (typeof hasAmeliaEntitiesApiCall !== 'undefined' && hasAmeliaEntitiesApiCall) {
    hasAmeliaEntitiesApiCall = false;
  }
</script>

<div id="amelia-v2-booking-<?php echo $params['counter']; ?>"
     class="amelia-v2-booking<?php echo $params['trigger'] !== '' ? ' amelia-skip-load amelia-skip-load-' . $params['counter'] : ''; ?>"
>
  <step-form-wrapper></step-form-wrapper>
</div>
