<?php defined('BASEPATH') or exit('No direct script access allowed');

$config['aws_config'] = true;

$config['s3_base_url'] = 'https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/';
$config['s3_categories'] = 'public/Categories';
$config['s3_resume'] = 'public/resume/';
$config['s3_themes'] = 'public/Themes/';
$config['s3_custom_themes'] = 'public/CustomThemes/';
$config['s3_custom_covers'] = 'public/CustomCovers/';
$config['s3_user_cover_img'] = 'public/AuthorCoverImages/';
$config['s3_covers'] = 'public/Covers/';
$config['s3_users_img']  = 'public/UserImages/';
$config['s3_author_img']  = 'public/AuthorImages/';
$config['s3_book_covers']  = 'public/BookCovers/';
$config['s3_site_images']  = 'public/SiteImages/';
$config['s3_users_img_nyaf']  = 'public/UserImagesNYAF/';
$config['s3_medallion_feedback']  = 'public/UserMedallionFeedback/';
$config['s3_user_gallery']  = 'public/EventGallery/';
$config['s3_crossword_store_images']  = 'public/CrossWordStoreImage/';
$config['s3_author_certificates'] = 'public/AuthorCertificate/';
$config['s3_school_certificates'] = 'public/SchoolCertificate/';
$config['s3_exhibition_images']  = 'public/ExhibitionImage/';

$config['cloudfront_url']  = 'https://media.bribooks.com/';
$config['front_url']  = 'https://master.d2ixqv5e8do7q.amplifyapp.com/';

define('S3_CERTIFICATE_URL', 'https://authorcertificates.s3.ap-south-1.amazonaws.com/');
