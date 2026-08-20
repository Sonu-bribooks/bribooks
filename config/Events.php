<?php defined('BASEPATH') OR exit('No direct script access allowed');

CI_Events::on('user_login', 'EventListener_lib::userLogin');
CI_Events::on('user_register', 'EventListener_lib::userRegister');

CI_Events::on('book_created', 'EventListener_lib::bookCreated');
CI_Events::on('book_updated', 'EventListener_lib::bookUpdated');
CI_Events::on('book_published', 'EventListener_lib::bookPublished');
CI_Events::on('book_reviewed', 'EventListener_lib::bookReviewed');

CI_Events::on('cart_created', 'EventListener_lib::cartCreated');
CI_Events::on('cart_updated', 'EventListener_lib::cartUpdated');

CI_Events::on('order_created', 'EventListener_lib::orderCreated');
CI_Events::on('order_updated', 'EventListener_lib::orderUpdated');
CI_Events::on('payment_created', 'EventListener_lib::paymentCreated');
CI_Events::on('printer_assigned', 'EventListener_lib::printerAssigned');
CI_Events::on('order_moved_to_afs', 'EventListener_lib::orderMovedToAfs');
CI_Events::on('order_ready_to_ship', 'EventListener_lib::orderReadyToShip');
CI_Events::on('order_shipped', 'EventListener_lib::orderShipped');
CI_Events::on('order_out_for_delivery', 'EventListener_lib::orderOutForDelivery');
CI_Events::on('order_delivered', 'EventListener_lib::orderDelivered');
CI_Events::on('order_undelivered', 'EventListener_lib::orderUndelivered');
CI_Events::on('order_returned', 'EventListener_lib::orderReturned');
CI_Events::on('order_canceled', 'EventListener_lib::orderCanceled');

CI_Events::on('order_confirmation_paperback', 'EventListener_lib::orderConfirmationPaperback');
CI_Events::on('order_confirmation_ebook', 'EventListener_lib::orderConfirmationEbook');
CI_Events::on('order_confirmation_audiobook', 'EventListener_lib::orderConfirmationAudiobook');

// Subscription
CI_Events::on('subscription_payment_created', 'EventListener_lib::subscriptionPaymentCreated');

CI_Events::on('access_log', 'EventListener_lib::accessLog');
CI_Events::on('system_access_log', 'EventListener_lib::systemAccessLog');

// CI_Events::on('event_student_signup_listener', 'MicroEventListener_lib::eventStudentSignupListener');
// CI_Events::on('event_school_signup', 'MicroEventListener_lib::eventSchoolSignup');
// CI_Events::on('send_medallion_message', 'MicroEventListener_lib::sendMedallionMessage');
// CI_Events::on('create_event_certificate', 'MicroEventListener_lib::createEventCertificate');
CI_Events::on('event_school_signup', 'NYAFEventListener_lib::eventSchoolSignup');
CI_Events::on('send_school_verification_email', 'NYAFEventListener_lib::sendSchoolVerificationEmail');
CI_Events::on('book_writing_log', 'NYAFEventListener_lib::bookWritingLog');
CI_Events::on('referral_student_signup', 'NYAFEventListener_lib::referralStudentSignup');

CI_Events::on('school_event_auto_enrol', 'GenericEventListener_lib::schoolEventAutoEnrol');
CI_Events::on('teacher_event_auto_enrol', 'GenericEventListener_lib::teacherEventAutoEnrol');

// event crm based Listener
CI_Events::on('event_signup', 'GenericEventListener_lib::eventSignup');

CI_Events::on('user_otp', 'MessageTemplateListener_lib::userOtp');
CI_Events::on('delivered_medallion_order', 'MessageTemplateListener_lib::deliveredMedallionOrder');
CI_Events::on('after_delivered_medallion_order', 'MessageTemplateListener_lib::afterDeliveredMedallionOrder');
CI_Events::on('subscription_purchase', 'MessageTemplateListener_lib::subscriptionPurchase');
CI_Events::on('subscription_expiry_reminder', 'MessageTemplateListener_lib::subscriptionExpiryReminder');
CI_Events::on('subscription_expired', 'MessageTemplateListener_lib::subscriptionExpired');
CI_Events::on('medallion_feedback', 'MessageTemplateListener_lib::medallionFeedback');
CI_Events::on('event_invite_verified', 'MessageTemplateListener_lib::eventInviteVerified');

// BRIMINDS 
CI_Events::on('bm_user_otp', 'BMMessageTemplateListener_lib::bmUserOtp');
CI_Events::on('bm_school_signup', 'BMMessageTemplateListener_lib::bmSchoolSignup');
CI_Events::on('bm_after_school_signup', 'BMMessageTemplateListener_lib::bmAfterSchoolSignup');

CI_Events::on('test_code','EventListener_lib::testCode');
