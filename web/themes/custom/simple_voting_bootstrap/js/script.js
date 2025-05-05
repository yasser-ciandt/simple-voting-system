(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.simpleVotingBootstrap = {
    attach: function (context, settings) {
      
      $('[data-toggle="tooltip"]', context).once('bootstrap-tooltips').tooltip();
      
      $('[data-toggle="popover"]', context).once('bootstrap-popovers').popover();
    }
  };

})(jQuery, Drupal);
