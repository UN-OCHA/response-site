<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\Traits;

/**
 * RSS test data.
 */
trait RssTestDataTrait {

  /**
   * Test data 1.
   */
  private function getTestRss1() {
    return <<<RSS
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
  <channel>
    <title>Drupal.org aggregator</title>
    <link>https://www.drupal.org/planet</link>
    <description>Drupal.org - aggregated feeds in category Planet Drupal</description>
    <language>en</language>
    <item>
      <title>DrupalCon News: Explore the Thriving Drupal Agency Ecosystem</title>
      <link>https://events.drupal.org/prague2022/news/explore-thriving-drupal-agency-ecosystem</link>
      <description></description>
      <pubDate>Mon, 25 Apr 2022 06:49:32 +0000</pubDate>
    </item>
    <item>
      <title>#! code: Drupal 9: Using The Caching API To Store Data</title>
      <link>https://www.hashbangcode.com/article/drupal-9-using-caching-api-store-data</link>
      <description>&lt;p&gt;The cache system in Drupal has a number of different components with time, contexts and tags
        being used to determine the cache.&lt;/p&gt;

        &lt;p&gt;&lt;a
        href=&quot;https://www.hashbangcode.com/article/drupal-9-using-caching-api-store-data&quot;&gt;Read
        more.&lt;/a&gt;&lt;/p&gt;
      </description>
      <pubDate>Sun, 24 Apr 2022 18:19:03 +0000</pubDate>
    </item>
    <item>
      <title>Docksal: Docksal 1.17.0 Release</title>
      <link>https://blog.docksal.io/docksal-1-17-0-release-c1d3261d7e3b?source=rss----11b90aebf34e--drupal</link>
      <description>&lt;p&gt;And a very special release it is!&lt;/p&gt;&lt;p&gt;A little pre-DrupalCon gift just
        dropped — Docksal &lt;a
        href=&quot;https://github.com/docksal/docksal/releases/tag/v1.17.0&quot;&gt;v1.17.0&lt;/a&gt;. This release now
        supports &lt;strong&gt;Apple M1&lt;/strong&gt; and &lt;strong&gt;Linux ARM64&lt;/strong&gt; architectures. That
        is just the tip of this iceberg of a release (albeit a very big tip for those that have been
        waiting).&lt;/p&gt;&lt;h3&gt;Software Version Updates&lt;/h3&gt;&lt;h4&gt;Docker
      </description>
      <pubDate>Sat, 23 Apr 2022 23:00:09 +0000</pubDate>
    </item>
    <item>
      <title>Centarro: The ABCs of PDPs and PLPs</title>
      <link>https://www.centarro.io/blog/abcs-pdps-and-plps</link>
      <description>While many Drupal developers have at least some eCommerce experience, the number of people in our
        community who make it their primary focus is rather small. This isn&#039;t surprising, given Drupal is most
        often used as a CMS, not an eCommerce platform. However, it does mean that when you encounter an eCommerce
        opportunity, you may not be making the most effective pitch you can to win the merchant&#039;s business.
      </description>
      <pubDate>Sat, 23 Apr 2022 00:53:07 +0000</pubDate>
    </item>
    <item>
      <title>Agiledrop.com Blog: Drupal DevDays 2022 – Revisiting my first in-person Drupal event</title>
      <link>https://www.agiledrop.com/blog/drupal-devdays-2022-revisiting-my-first-person-drupal-event</link>
      <description>&lt;div&gt;&lt;a
        href=&quot;https://www.agiledrop.com/blog/drupal-devdays-2022-revisiting-my-first-person-drupal-event&quot;&gt;
        &lt;/a&gt;&lt;a
        href=&quot;https://www.agiledrop.com/blog/drupal-devdays-2022-revisiting-my-first-person-drupal-event&quot;
        hreflang=&quot;en&quot;&gt;&lt;img
        src=&quot;https://www.agiledrop.com/sites/default/files/styles/blog_m/public/2022-04/20220404_094348%20%284%29.jpeg?itok=QB20mhUT&quot;
        width=&quot;400&quot; height=&quot;200&quot; alt=&quot;Jure at Agiledrop&#039;s booth at Drupal DevDays
        2022&quot; /&gt;
      </description>
      <pubDate>Fri, 22 Apr 2022 07:40:22 +0000</pubDate>
    </item>
    <item>
      <title>Opensource.com: 3 things to know about Drupal in 2022</title>
      <link>https://opensource.com/article/22/4/new-drupal-features-2022</link>
      <description></description>
      <pubDate>Fri, 22 Apr 2022 07:00:00 +0000</pubDate>
    </item>
    <item>
      <title>Oomph Insights: Elevating Our Impact: Oomph Joins 1% for the Planet</title>
      <link>https://www.oomphinc.com/insights/1-percent-for-the-planet</link>
      <description>I’ve been following 1% for the Planet since around 2006, before Oomph was even born. As a father of a
        one-year-old at the time and a passionate outdoor enthusiast — especially around the ocean — sustainability was
        always top of mind for me, and seeing businesses take a stand on these issues was incredibly inspiring. I used
        to dream that one day my company would be a member, committed to giving back to the environment. Today, I’m
        excited to announce that Oomph has joined 1% for the Planet for 2022 and beyond! 1% for the Planet pairs
        businesses and individuals with environmental nonprofits…</description>
      <pubDate>Fri, 22 Apr 2022 00:00:00 +0000</pubDate>
    </item>
    <item>
      <title>ImageX: Top Upcoming DrupalCon 2022 Picks for Higher Ed Marketers</title>
      <link>https://imagexmedia.com/blog/drupalcon-2022-higher-ed-sessions</link>
      <description>Oxford University, Harvard, MIT, Stanford, and a great number of other top Higher Ed institutions
        have one thing in common. Their websites are built with Drupal, the open source platform that keeps innovating
        to provide cutting-edge digital experiences.

        What are the latest Drupal improvements, top-notch features, best practices, or complementary technologies that
        could be beneficial for university and college websites? The best way to learn is to follow the Drupal
        community’s largest annual event — DrupalCon. It gathers the best Drupal minds and the most passionate
        innovators that share their ideas, drive change and shape Drupal’s future.</description>
      <pubDate>Thu, 21 Apr 2022 16:20:40 +0000</pubDate>
    </item>
    <item>
      <title>ImageX: DrupalCon Portland: Top Sessions for Developers</title>
      <link>https://imagexmedia.com/blog/drupalcon-2022-developer-sessions</link>
      <description>Between April 25 to April 28, 2022, Portland will become the Drupal capital of the world. Like the
        strongest of magnets, the Oregon Convention Center will attract thousands of attendees to the first in-person
        DrupalCon since 2019.

        Valuable insights, new connections, and lots of communication are guaranteed for marketers, designers, editors,
        project managers, and all others who create or work with Drupal websites. We have recently shared top DrupalCon
        picks for Higher Ed marketers which is worth checking out.</description>
      <pubDate>Thu, 21 Apr 2022 16:19:20 +0000</pubDate>
    </item>
    <item>
      <title>ImageX: Top Session Choices for Non-Profit Marketers at DrupalCon Portland</title>
      <link>https://imagexmedia.com/blog/drupalcon-2022-nonprofit-sessions</link>
      <description>Non-profit teams are dedicated enthusiasts working for a given cause. They definitely know the worth
        of a good website. Like a tireless team member, websites can work 24/7 bringing the organization’s message
        across to potential donors, boosting the whole team’s efficiency in routine tasks, and providing smooth user
        experiences for everyone.

        An enthusiastic team member always strives to go the extra mile. Similarly, non-profit websites can always do
        more for their teams... But in what way? The best answers can be found at DrupalCon Portland 2022.</description>
      <pubDate>Thu, 21 Apr 2022 16:18:48 +0000</pubDate>
    </item>
    <item>
      <title>Aten Design Group: Set up a local Drupal multisite with Lando on Mac OS</title>
      <link>https://atendesigngroup.com/articles/set-local-drupal-multisite-lando-mac-os</link>
      <description></description>
      <pubDate>Thu, 21 Apr 2022 11:34:32 +0000</pubDate>
    </item>
    <item>
      <title>Morpht: Announcing the Field Formatter Pattern module</title>
      <link>https://www.morpht.com/blog/announcing-field-formatter-pattern-module</link>
      <description>A new contributed Drupal module allows site builders to add custom HTML pattern attribute to the text
        fields in the Manage Form Display settings page.</description>
      <pubDate>Thu, 21 Apr 2022 01:24:00 +0000</pubDate>
    </item>
    <item>
      <title>Morpht: Announcing the Search API Field Token module</title>
      <link>https://www.morpht.com/blog/announcing-search-api-field-token-module</link>
      <description>The Drupal Search API Field Token module allows to send data into search index using tokens.
      </description>
      <pubDate>Thu, 21 Apr 2022 01:24:00 +0000</pubDate>
    </item>
    <item>
      <title>Drupal.org blog: What’s new on Drupal.org - Q1 2022</title>
      <link>https://www.drupal.org/drupalorg/blog/whats-new-on-drupalorg-q1-2022</link>
      <description></description>
      <pubDate>Wed, 20 Apr 2022 23:16:17 +0000</pubDate>
    </item>
    <item>
      <title>Lullabot: The Present and Future of Drupal’s Administrative Interface</title>
      <link>https://www.lullabot.com/articles/present-and-future-drupals-administrative-interface</link>
      <description>&lt;p&gt;Claro is the new core administration theme based on the new &lt;a
        href=&quot;https://www.drupal.org/docs/core-modules-and-themes/core-themes/claro-theme/design&quot;&gt;Drupal
        design system&lt;/a&gt;. It is a clone of the Seven admin theme, the default admin theme in Drupal since Drupal
        7. If Seven is adequate for a certain need, then Claro will be adequate for that need, too. We didn’t have to
        start from scratch.&lt;/p&gt;

        &lt;p&gt;The big-picture goals are as follows:&lt;/p&gt;</description>
      <pubDate>Wed, 20 Apr 2022 18:43:29 +0000</pubDate>
    </item>
  </channel>
</rss>
RSS;
  }

}
