<?php

const SC_LOCAL_ENV = false;

const SMARTCAT_API_PREFIX = 'smartcat';

const SMARTCAT_DEV_MODE = false;

const SMARTCAT_LOGS_SECRET = 'RIq91EvJmozX6v4KFSyV';

const SMARTCAT_HOST = 'https://smartcat.com';
const SMARTCAT_IHUB_HOST = 'https://ihub.smartcat.com';
const SMARTCAT_HOST_DEV = 'http://localhost:5034';
const SMARTCAT_IHUB_HOST_DEV = 'http://localhost:5002/';

const SMARTCAT_IHUB_HOSTS = [
    'eu' => 'https://ihub.smartcat.com',
    'ea' => 'https://ihub-ea.smartcat.com',
    'us' => 'https://ihub-us.smartcat.com'
];

const SMARTCAT_HOSTS = [
    'eu' => 'https://smartcat.com',
    'ea' => 'https://ea.smartcat.com',
    'us' => 'https://us.smartcat.com'
];

const SMARTCAT_REQUIRED_SYSTEM_POST_METADATA = [
    '_yoast_wpseo_focuskeywords',
    '_yoast_wpseo_metadesc',
    '_yoast_wpseo_title',
];

const SMARTCAT_IGNORE_POST_TYPES = [
    'attachment',
];

const SMARTCAT_DEFAULT_WPML_STRINGS_DOMAINS = [
    'block-widget',
    'Widgets'
];

const SC_WP_BAKERY_BASE64_TAGS = [
    'vc_raw_html',
    'vc_raw_js'
];

const SC_PROJECT_WORKFLOW_STAGES = [
    'mt' => [1],
    'mt-postediting' => [1, 7],
    'manual' => []
];

const SC_PROJECT_EXTERNAL_TAG = 'ihub-wordpress-app';

const SMARTCAT_DOCUMENTS_TABLE_NAME = 'smartcat_documents';
const SMARTCAT_LOGS_TABLE_NAME = 'smartcat_events';
const SMARTCAT_CREATE_TRANSLATION_BULK_ACTION = 'smartcat_create_translation_request';

const SC_SENTRY_PROJECT_ID = 6661941;
const SC_SENTRY_KEY = '3239afe712a744abba1edbc3737b92fc';

const SC_CRON_GET_TRANSLATIONS = 'get_translations';

const SC_CRON_EVERY_MINUTE = 'sc_every_minute';
const SC_CRON_EVERY_2_MINUTE = 'sc_every_2_minute';
const SC_CRON_EVERY_10_MINUTES = 'sc_every_10_minutes';
const SC_CRON_EVERY_15_MINUTES = 'sc_every_15_minutes';
const SC_CRON_EVERY_HOUR = 'sc_every_1_hour';

const SMARTCAT_ELEMENTOR_TYPES = [
    'call-to-action' => \Smartcat\Includes\Services\Elementor\Models\CallToAction::class,
    'image-box' => \Smartcat\Includes\Services\Elementor\Models\ImageBox::class,
    'heading' => \Smartcat\Includes\Services\Elementor\Models\Heading::class,
    'text-editor' => \Smartcat\Includes\Services\Elementor\Models\TextEditor::class,
    'button' => \Smartcat\Includes\Services\Elementor\Models\Button::class,
    'posts' => \Smartcat\Includes\Services\Elementor\Models\Posts::class,
    'form' => \Smartcat\Includes\Services\Elementor\Models\Form::class,
    'slides' => \Smartcat\Includes\Services\Elementor\Models\Slides::class,
    'animated-headline' => \Smartcat\Includes\Services\Elementor\Models\AnimatedHeadline::class,
    'price-list' => \Smartcat\Includes\Services\Elementor\Models\PriceList::class,
    'price-table' => \Smartcat\Includes\Services\Elementor\Models\PriceTable::class,
    'flip-box' => \Smartcat\Includes\Services\Elementor\Models\FlipBox::class,
    'testimonial-carousel' => \Smartcat\Includes\Services\Elementor\Models\TestimonialCarousel::class,
    'reviews' => \Smartcat\Includes\Services\Elementor\Models\Reviews::class,
    'table-of-contents' => \Smartcat\Includes\Services\Elementor\Models\TableOfContents::class,
    'blockquote' => \Smartcat\Includes\Services\Elementor\Models\Blockquote::class,
    'icon-box' => \Smartcat\Includes\Services\Elementor\Models\IconBox::class,
    'icon-list' => \Smartcat\Includes\Services\Elementor\Models\IconList::class,
    'progress' => \Smartcat\Includes\Services\Elementor\Models\Progress::class,
    'testimonial' => \Smartcat\Includes\Services\Elementor\Models\Testimonial::class,
    'tabs' => \Smartcat\Includes\Services\Elementor\Models\Tabs::class,
    'accordion' => \Smartcat\Includes\Services\Elementor\Models\Accordion::class,
    'toggle' => \Smartcat\Includes\Services\Elementor\Models\Toggle::class,
    'alert' => \Smartcat\Includes\Services\Elementor\Models\Alert::class,
    'text-path' => \Smartcat\Includes\Services\Elementor\Models\TextPath::class,
    'star-rating' => \Smartcat\Includes\Services\Elementor\Models\StarRating::class,
    'counter' => \Smartcat\Includes\Services\Elementor\Models\Counter::class,
    'jet-carousel' => \Smartcat\Includes\Services\Elementor\Models\JetCarousel::class,
    'jet-animated-box' => \Smartcat\Includes\Services\Elementor\Models\JetAnimatedBox::class,
    'jet-animated-text' => \Smartcat\Includes\Services\Elementor\Models\JetAnimatedText::class,
    'jet-banner' => \Smartcat\Includes\Services\Elementor\Models\JetBanner::class,
    'jet-brands' => \Smartcat\Includes\Services\Elementor\Models\JetBrands::class,
    'jet-button' => \Smartcat\Includes\Services\Elementor\Models\JetButton::class,
    'jet-download-button' => \Smartcat\Includes\Services\Elementor\Models\JetDownloadButton::class,
    'jet-dropbar' => \Smartcat\Includes\Services\Elementor\Models\JetDropbar::class,
    'jet-headline' => \Smartcat\Includes\Services\Elementor\Models\JetHeadline::class,
    'jet-horizontal-timeline' => \Smartcat\Includes\Services\Elementor\Models\JetHorizontalTimeline::class,
    'jet-portfolio' => \Smartcat\Includes\Services\Elementor\Models\JetPortfolio::class,
    'jet-price-list' => \Smartcat\Includes\Services\Elementor\Models\JetPriceList::class,
    'jet-pricing-table' => \Smartcat\Includes\Services\Elementor\Models\JetPricingTable::class,
    'jet-progress-bar' => \Smartcat\Includes\Services\Elementor\Models\JetProgressBar::class,
    'jet-services' => \Smartcat\Includes\Services\Elementor\Models\JetServices::class,
    'jet-slider' => \Smartcat\Includes\Services\Elementor\Models\JetSlider::class,
    'jet-table' => \Smartcat\Includes\Services\Elementor\Models\JetTable::class,
    'jet-team-member' => \Smartcat\Includes\Services\Elementor\Models\JetTeamMember::class,
    'jet-testimonials' => \Smartcat\Includes\Services\Elementor\Models\JetTestimonials::class,
    'jet-timeline' => \Smartcat\Includes\Services\Elementor\Models\JetTimeline::class,
    'jet-accordion' => \Smartcat\Includes\Services\Elementor\Models\JetAccordion::class,
    'jet-image-accordion' => \Smartcat\Includes\Services\Elementor\Models\JetImageAccordion::class,
    'jet-tabs' => \Smartcat\Includes\Services\Elementor\Models\JetTabs::class,
    'jet-unfold' => \Smartcat\Includes\Services\Elementor\Models\JetUnfold::class,
    'jet-view-more' => \Smartcat\Includes\Services\Elementor\Models\JetViewMore::class,
];

const SC_WP_BAKERY_SINGLE_TAGS = [
    'vc_masonry_media_grid',
    'vc_media_grid',
    'vc_basic_grid',
    'vc_masonry_grid',
    'vc_empty_space',
    'vc_line_chart',
    'vc_round_chart',
    'vc_pie',
    'vc_progress_bar',
    'vc_flickr',
    'vc_gmaps',
    'vc_video',
    'vc_posts_slider',
    'vc_images_carousel',
    'vc_gallery',
    'vc_single_image',
    'vc_pinterest',
    'vc_googleplus',
    'vc_tweetmeme',
    'vc_facebook',
    'vc_zigzag',
    'vc_separator',
    'vc_text_separator',
    'vc_icon',
    'vc_widget_sidebar',
    'vc_btn',
    'vc_custom_heading',
    'vc_acf',
    'vc_wp_search',
    'vc_wp_meta',
    'vc_wp_recentcomments',
    'vc_wp_calendar',
    'vc_wp_pages',
    'vc_wp_tagcloud',
    'vc_wp_custommenu',
    'vc_wp_posts',
    'vc_wp_categories',
    'vc_wp_archives',
    'vc_wp_rss',
];
