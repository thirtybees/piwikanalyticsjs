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
{capture name="modalHtml"}{strip}{include file="./matomo_site_lookup_modal.tpl"}{/strip}{/capture}
{capture name="siteIdInput"}{strip}{include file="./matomo_site_lookup_siteid.tpl"}{/strip}{/capture}
{capture name="authTokenInput"}{strip}{include file="./matomo_site_lookup_auth_token.tpl"}{/strip}{/capture}
<script type="text/javascript">
  var idPKSiteId = 'PIWIK_SITEID';
  var idPKToken = 'PIWIK_TOKEN_AUTH';
  var htmls000 = '{$smarty.capture.modalHtml|escape:'javascript'}';
  var htmls001 = '{$smarty.capture.siteIdInput|escape:'javascript'}';
  var htmls002 = '{$smarty.capture.authTokenInput|escape:'javascript'}';
  $(document).ready(function () {
    if (($('#' + idPKSiteId).val() === '') || ($('#' + idPKSiteId).val() === '0') || (parseInt($('#' + idPKSiteId).val()) <= 0)) {
      $('#' + idPKSiteId).parent().find("sup").after(htmls001);
    }
    if ($('#' + idPKToken).val() === '') {
      $('#' + idPKToken).parent().find("sup").after(htmls002);
    }
    $('#PiwikLookupModal').html(htmls000);

  });
  var _upPopO = false;

  function PiwikLookup() {
    $('#PiwikLookupModal').html(htmls000);
    if (_upPopO) {
      _upPopO = false;
      $('#PiwikLookupModal').css({ opacity: 0, 'pointer-events': 'none' });
    } else {
      _upPopO = true;
      $('#PiwikLookupModal').css({ opacity: 1, 'pointer-events': 'auto' });
      $('#PiwikLookupLoginFormUsername').focus();
    }
    return false;
  }

  function PiwikLookupLogin(e) {
    if (e instanceof KeyboardEvent && e.key !== 'Enter') {
      return;
    }
    e.preventDefault();
    var username = $('#PiwikLookupLoginFormUsername').val(),
      password = $('#PiwikLookupLoginFormPassword').val(),
      httpUsername = $('#PiwikLookupLoginFormHttpAuthUsername').val(),
      httpPassword = $('#PiwikLookupLoginFormHttpAuthPassword').val(),
      authtoken = '',
      piwikhost = '';

    if (httpUsername === "" || httpUsername === false) {
      httpUsername = $('#PIWIK_PAUTHUSR').val();
    }
    if (httpPassword === "" || httpPassword === false) {
      httpPassword = $('#PIWIK_PAUTHPWD').val();
    }
    {* Http auth *}
    $('#PIWIK_PAUTHUSR').val(httpUsername);
    $('#PIWIK_PAUTHPWD').val(httpPassword);
    {* Piwik login *}
    $('#PIWIK_USRNAME').val(username);
    $('#PIWIK_USRPASSWD').val(password);


      swal({
        text: '{l s='Please enter the Matomo host' mod='piwikanalyticsjs' js=1}',
        content: {
          element: 'input',
          attributes: {
            placeholder: $('#PIWIK_HOST').val().trim() === '' ? 'matomo.example.com/' : $('#PIWIK_HOST').val().trim(),
            type: 'text',
          },
        },
        buttons: {
          confirm: {
            text: '{l s='OK' mod='piwikanalyticsjs' js=1}',
            closeModal: false,
            visible: true,
          },
          cancel: {
            text: '{l s='Cancel' mod='piwikanalyticsjs' js=1}',
            closeModal: true,
            visible: true,
            value: 'cancel'
          }
        },
      }).then(function (input) {
        if (input === 'cancel') {
          swal.close();
          return;
        }
        swal({
          text: '{l s='Connect via HTTPS?' mod='piwikanalyticsjs' js=1}',
          buttons: {
            confirm: {
              text: '{l s='YES' mod='piwikanalyticsjs' js=1}',
              closeModal: false,
              value: true,
              visible: true,
            },
            cancel: {
              text: '{l s='NO' mod='piwikanalyticsjs' js=1}',
              closeModal: true,
              value: null,
              visible: true,
            }
          },
        }).then(function (https) {
          piwikhost = $('#PIWIK_HOST').val().trim();
          if (input) {
            piwikhost = input;
          }
          piwikhost = piwikhost.replace("http://", "").replace("https://", "").replace("://", "").replace("//", "").replace(/\/+$/, '') + '/';
          $('#PIWIK_HOST').val(piwikhost);
          if (https) {
            $('input[name=PIWIK_CRHTTPS]')[0].checked = true;
          } else {
            $('input[name=PIWIK_CRHTTPS]')[1].checked = true;
          }

          /* get auth token */
          $.ajax({
            type: 'POST',
            url: window.currentIndex + '&configure=piwikanalyticsjs&token=' + window.token,
            dataType: 'json',
            data: {
              'pkapicall': 'getTokenAuth',
              'userLogin': username,
              'password': password,
              'httpUser': httpUsername,
              'httpPasswd': httpPassword,
              'piwikhost': piwikhost
            },
            success: function (data) {
              if (data.error === true) {
                swal({
                  icon: 'error',
                  text: data.message
                });
              } else {
                authtoken = data.message;
                $('#PIWIK_TOKEN_AUTH').val(authtoken);
                var xhr = $.ajax({
                  type: 'POST',
                  url: (!https ? 'http://' : 'https://') + piwikhost + 'index.php?module=API&token_auth=' + authtoken + '&method=SitesManager.getSitesWithAdminAccess&format=JSON',
                  dataType: 'json',
                  beforeSend: function (xhr) {
                    if (httpUsername && httpPassword) {
                      xhr.setRequestHeader('Authorization', 'Basic ' + btoa(httpUsername + ':' + httpPassword));
                    }
                  },
                  success: function (data) {
                    swal.close();
                    if (data.error === true) {
                      swal({
                        icon: 'error',
                        title: '{l s='An error occurred' mod='piwikanalyticsjs' js=1}',
                        text: data.message
                      });
                      PiwikLookup();
                    } else {
                      var siteshtml = "";
                      for (var i = 0, max = data.length; i < max; i++) {
                        siteshtml += "<li style='cursor: pointer; color: black;' onclick='PiwikLookupSetSiteId(" + data[i].idsite + ");'>" + data[i].name + " #" + data[i].idsite + "</li>";
                      }
                      var html = [
                        '<style>.tyujhgfdc li:hover{ background-color:#EEEEEE; }</style><div><i class="icon icon-2x icon-times-circle pull-right PiwikLookupClose" onclick="PiwikLookup();" title="{l s='Close' mod='piwikanalyticsjs' js=1}"></i>',
                        '<h2>{l s='Select the site you are setting up.' mod='piwikanalyticsjs' js=1}</h2>',
                        '<p><ul class="tyujhgfdc">' + siteshtml + '</ul>',
                        '</p></div>',
                      ];
                      $("#PiwikLookupModal").html(html.join(""));
                    }
                  },
                  error: function (XMLHttpRequest, textStatus, errorThrown) {
                    swal({
                      icon: 'error',
                      title: '{l s='Error while fetching Matomo Data' mod='piwikanalyticsjs' js=1}',
                      text: "{l s='Check if the hostname is correct and/or CORS has been enabled (required).' mod='piwikanalyticsjs' js=1}\n\ntextStatus: " + textStatus + "\nerrorThrown: '" + errorThrown + "\nresponseText:\n" + XMLHttpRequest.responseText
                    });
                    console.log(xhr);
                  },
                });
              }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
              var elem = document.createElement('P');
              elem.innerHTML =
              swal({
                icon: 'error',
                title: '{l s='Error while fetching Matomo Data' mod='piwikanalyticsjs' js=1}',
                text: "{l s='Check if the hostname is correct and/or CORS has been enabled (required).' mod='piwikanalyticsjs' js=1}\n\ntextStatus: " + textStatus + "\nerrorThrown: " + errorThrown + "\nresponseText:\n" + XMLHttpRequest.responseText
              });
            },
          });
        });
      });
    return false;
  }

  function PiwikLookupSetSiteId(id) {
    $('#PIWIK_SITEID').val(id);
    PiwikLookup();
    if ($('#configuration_form').length > 0) {
      $('#configuration_form').submit();
    }
  }
</script>
<div id="PiwikLookupModal" class="PiwikLookupModalDialog"></div>
