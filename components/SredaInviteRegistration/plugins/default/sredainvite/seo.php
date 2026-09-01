<?php
$seoTitle       = ossn_print('sreda:seo:title');
$seoDescription = ossn_print('sreda:seo:description');
$seoImageAlt    = ossn_print('sreda:seo:image:alt');
$seoSiteUrl     = rtrim(ossn_site_url(), '/') . '/';
$seoImageUrl    = ossn_site_url('sreda_opengraf.png');
$seoEscape      = static function ($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<meta name="description" content="<?php echo $seoEscape($seoDescription); ?>" />
<link rel="canonical" href="<?php echo $seoEscape($seoSiteUrl); ?>" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="SREDA" />
<meta property="og:locale" content="ru_RU" />
<meta property="og:title" content="<?php echo $seoEscape($seoTitle); ?>" />
<meta property="og:description" content="<?php echo $seoEscape($seoDescription); ?>" />
<meta property="og:url" content="<?php echo $seoEscape($seoSiteUrl); ?>" />
<meta property="og:image" content="<?php echo $seoEscape($seoImageUrl); ?>" />
<meta property="og:image:type" content="image/png" />
<meta property="og:image:alt" content="<?php echo $seoEscape($seoImageAlt); ?>" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?php echo $seoEscape($seoTitle); ?>" />
<meta name="twitter:description" content="<?php echo $seoEscape($seoDescription); ?>" />
<meta name="twitter:image" content="<?php echo $seoEscape($seoImageUrl); ?>" />
