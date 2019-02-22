jQuery( document ).ready(function() {
    jQuery('#employer_id').parent().hide();
    jQuery('#job_title').parent().hide();
    jQuery('#Email_Block_1,#Email_Block_2,#Email_Block_3').find(':checkbox').parent().hide();
    jQuery("a[title='Email Hold Help']").parent().hide();
    jQuery("a[title='Bulk Mailings Help']").parent().hide()
});
