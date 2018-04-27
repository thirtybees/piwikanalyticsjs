{*
 * 2007-2016 PrestaShop
 *
 * thirty bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017-2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2018 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * PrestaShop is an internationally registered trademark & property of PrestaShop SA
*}
<script type="text/javascript">
  function submitPiwikSiteAPIUpdate() {
    var ajaxes = [];
    if ($('#fieldset_2_2').is(':visible')) {
      var idSite = $('#PKAdminIdSite').val();
      var siteName = $('#PKAdminSiteName').val();
      /*var urls = $('#PKAdminSiteUrls').val();*/
      var ecommerce = $('input[name=PKAdminEcommerce]:checked').val();
      var siteSearch = $('input[name=PKAdminSiteSearch]:checked').val();
      if ($.isFunction($.fn.tagify)) {
        $(this).find('#PKAdminSearchKeywordParameters').val($('#PKAdminSearchKeywordParameters').tagify('serialize'));
      }
      var searchKeywordParameters = $('#PKAdminSearchKeywordParameters').val();
      if ($.isFunction($.fn.tagify)) {
        $(this).find('#PKAdminSearchCategoryParameters').val($('#PKAdminSearchCategoryParameters').tagify('serialize'));
      }
      var searchCategoryParameters = $('#PKAdminSearchCategoryParameters').val();
      if ($.isFunction($.fn.tagify)) {
        $(this).find('#PKAdminExcludedIps').val($('#PKAdminExcludedIps').tagify('serialize'));
      }
      var excludedIps = $('#PKAdminExcludedIps').val();
      if ($.isFunction($.fn.tagify)) {
        $(this).find('#PKAdminExcludedQueryParameters').val($('#PKAdminExcludedQueryParameters').tagify('serialize'));
      }
      var excludedQueryParameters = $('#PKAdminExcludedQueryParameters').val();
      var timezone = $('#PKAdminTimezone').val();
      var currency = $('#PKAdminCurrency').val();
      /*var group = $('#PKAdminGroup').val();*/
      /*var startDate = $('#PKAdminStartDate').val();*/
      var excludedUserAgents = $('#PKAdminExcludedUserAgents').val();
      var keepURLFragments = $('input[name=PKAdminKeepURLFragments]:checked').val();
      /*var type = $('#PKAdminSiteType').val();*/
      ajaxes.push($.ajax({
        type: 'POST',
        url: '{$psm_currentIndex|escape:'javascript'}&token={$psm_token|escape:'javascript'}',
        dataType: 'json',
        data: {
          pkapicall: 'updatePiwikSite',
          ajax: 1,
          idSite: idSite,
          siteName: siteName,
          ecommerce: ecommerce,
          siteSearch: siteSearch,
          searchKeywordParameters: searchKeywordParameters,
          searchCategoryParameters: searchCategoryParameters,
          excludedIps: excludedIps,
          excludedQueryParameters: excludedQueryParameters,
          timezone: timezone,
          currency: currency,
          keepURLFragments: keepURLFragments,
          /*group: group, */
          excludedUserAgents: excludedUserAgents,
        },
        beforeSend: function () {
          showLoadingStuff();
        },
      }));
    }

    if (typeof FormData !== 'undefined') {
      var formData = new FormData(document.getElementById('configuration_form'));
      formData.append('configuration_form_submit_btn', '1');
      formData.append('submitUpdatepiwikanalyticsjs', '1');
      ajaxes.push($.ajax({
        type: 'POST',
        url: '{$psm_currentIndex|escape:'javascript'}&token={$psm_token|escape:'javascript'}',
        processData: false,
        dataType: false,
        contentType: false,
        data: formData,
      }));
    } else {
      $('#configuration_form_submit_btn').click();
      return false;
    }

    if (ajaxes.length) {
      $.when.apply($, ajaxes)
        .then(function (api) {
          if (typeof FormData !== 'undefined') {
            swal({
              icon: 'success',
              text: api[0].message
            });
          } else {
            $('#configuration_form_submit_btn').click();
          }
        })
        .fail(function (api, config) {
          swal({
            icon: 'error',
            text: "Error while saving Piwik Data\n\ntextStatus: '" + (api[0] || config[0]) + "'\nerrorThrown: '" + (api[1] || config[1]) + "'\nresponseText:\n" + (api[2].responseText || config[2].responseText)
          });
        })
        .done(function () {
          hideLoadingStuff();
        })
      ;
    } else if (typeof FormData === 'undefined') {
      $('#configuration_form_submit_btn').click();
    }

    return false;
  }

  function hideLoadingStuff() {
    $('#ajax_running').hide('fast');
    clearTimeout(ajax_running_timeout);
    $.fancybox.helpers.overlay.close();
    $.fancybox.hideLoading();
  }

  function showLoadingStuff() {
    showAjaxOverlay();
    $.fancybox.helpers.overlay.open({ parent: $('body') });
    $.fancybox.showLoading();
  }

  function ChangePKSiteEdit(id) {
    $.ajax({
      type: 'POST',
      url: '{$psm_currentIndex}&token={$psm_token}',
      dataType: 'json',
      data: {
        'pkapicall': 'getPiwikSite',
        'idSite': id,
      },
      beforeSend: function () {
        showLoadingStuff();
      },
      success: function (data) {
        /* $('#SPKSID').val(data.message[0].idSite);  */
        $('#PKAdminIdSite').val(data.message[0].idsite);
        $('#PKAdminSiteName').val(data.message[0].name);
        $('#wnamedsting').text(data.message[0].name);
        /*$('#PKAdminSiteUrls').val(data.message[0].main_url);*/
        {if $psversion >= '1.6'}
        if (data.message[0].ecommerce === 1) {
          $('#PKAdminEcommerce_on').prop('checked', true);
          $('#PKAdminEcommerce_off').prop('checked', false);
        } else {
          $('#PKAdminEcommerce_off').prop('checked', true);
          $('#PKAdminEcommerce_on').prop('checked', false);
        }
        if (data.message[0].sitesearch === 1) {
          $('#PKAdminSiteSearch_on').prop('checked', true);
          $('#PKAdminSiteSearch_off').prop('checked', false);
        } else {
          $('#PKAdminSiteSearch_off').prop('checked', true);
          $('#PKAdminSiteSearch_on').prop('checked', false);
        }
        {else}
        if (data.message[0].ecommerce === 1) {
          $('input[id=active_on][name=PKAdminEcommerce]').attr('checked', true);
          $('input[id=active_off][name=PKAdminEcommerce]').attr('checked', false);
        } else {
          $('input[id=active_off][name=PKAdminEcommerce]').attr('checked', true);
          $('input[id=active_on][name=PKAdminEcommerce]').attr('checked', false);
        }
        if (data.message[0].sitesearch === 1) {
          $('input[id=active_on][name=PKAdminSiteSearch]').attr('checked', true);
          $('input[id=active_off][name=PKAdminSiteSearch]').attr('checked', false);
        } else {
          $('input[id=active_off][name=PKAdminSiteSearch]').attr('checked', true);
          $('input[id=active_on][name=PKAdminSiteSearch]').attr('checked', false);
        }
        {/if}
        $('#PKAdminSearchKeywordParameters').val(data.message[0].sitesearch_keyword_parameters);
        $('#PKAdminSearchCategoryParameters').val(data.message[0].sitesearch_category_parameters);
        $('#PKAdminExcludedIps').val(data.message[0].excluded_ips);
        $('#PKAdminExcludedQueryParameters').val(data.message[0].excluded_parameters);
        $('#PKAdminTimezone').val(data.message[0].timezone);
        $('#PKAdminCurrency').val(data.message[0].currency);
        /*$('#PKAdminGroup').val(data.message[0].group);*/
        /*$('#PKAdminStartDate').val(data.message[0].ts_created);*/
        $('#PKAdminExcludedUserAgents').val(data.message[0].excluded_user_agents);

        {if $psversion >= '1.6'}
        if (data.message[0].keep_url_fragment === 1) {
          $('#PKAdminKeepURLFragments_on').prop('checked', true);
          $('#PKAdminKeepURLFragments_off').prop('checked', false);
        } else {
          $('#PKAdminKeepURLFragments_off').prop('checked', true);
          $('#PKAdminKeepURLFragments_on').prop('checked', false);
        }
        {else}
        if (data.message[0].keep_url_fragment === 1) {
          $('input[id=active_on][name=PKAdminKeepURLFragments]').attr('checked', true);
          $('input[id=active_off][name=PKAdminKeepURLFragments]').attr('checked', false);
        } else {
          $('input[id=active_off][name=PKAdminKeepURLFragments]').attr('checked', true);
          $('input[id=active_on][name=PKAdminKeepURLFragments]').attr('checked', false);
        }
        {/if}
        /*$('#PKAdminSiteType').val(data.message[0].type);*/
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        swal({
          icon: 'error',
          text: "Error while saving Piwik Data\n\ntextStatus: '" +textStatus + "'\nerrorThrown: '" + errorThrown + "'\nresponseText:\n" + XMLHttpRequest.responseText
        });
      },
      complete: function () {
        hideLoadingStuff();
      }
    });
  }
</script>
