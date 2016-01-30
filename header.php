<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>

<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
  <channel>
    <title><?php echo $feed_title;?></title>
    <link><?php echo $base_url;?></link>
    <language><?php echo $feed_language;?></language>
    <copyright><?php echo $feed_copyright;?></copyright>
    <description><?php echo $feed_description;?></description>
    <lastBuildDate><?php echo date(DateTime::RFC2822, $last_build_date);?></lastBuildDate>
    <pubDate><?php echo date(DateTime::RFC2822, $last_build_date);?></pubDate>
    <docs>http://blogs.law.harvard.edu/tech/rss</docs>
    <webMaster><?php echo $feed_email;?> (<?php echo $feed_author;?>)</webMaster>
    <itunes:subtitle>shows</itunes:subtitle>
    <itunes:author><?php echo $feed_author;?></itunes:author>
    <itunes:summary></itunes:summary>
    <itunes:image href="<?php echo $base_url . $image_url ;?>" />
    <itunes:owner>
      <itunes:name><?php echo $feed_author;?></itunes:name>
      <itunes:email><?php echo $feed_email;?> (<?php echo $feed_author;?>)</itunes:email>
    </itunes:owner>
    <itunes:category text="Music"/>
    <itunes:explicit>no</itunes:explicit>

