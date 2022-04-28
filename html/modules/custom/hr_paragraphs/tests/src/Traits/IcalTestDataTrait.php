<?php

// phpcs:ignoreFile

namespace Drupal\Tests\hr_paragraphs\Traits;

/**
 * RSS test data.
 */
trait IcalTestDataTrait {

  /**
   * Test data 1.
   */
  private function getTestIcal1() {
    $yearmonth = date('Ym');

    return <<<ICAL
    BEGIN:VCALENDAR
    PRODID:-//Google Inc//Google Calendar 70.9054//EN
    VERSION:2.0
    CALSCALE:GREGORIAN
    METHOD:PUBLISH
    X-WR-CALNAME:AFG - HumanitarianResponse.info (test)
    X-WR-TIMEZONE:Asia/Kabul
    X-WR-CALDESC:This is a test Google Calendar for possible use on HR.info as
     the calendar service
    BEGIN:VTIMEZONE
    TZID:Europe/Rome
    X-LIC-LOCATION:Europe/Rome
    BEGIN:DAYLIGHT
    TZOFFSETFROM:+0100
    TZOFFSETTO:+0200
    TZNAME:CEST
    DTSTART:19700329T020000
    RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
    END:DAYLIGHT
    BEGIN:STANDARD
    TZOFFSETFROM:+0200
    TZOFFSETTO:+0100
    TZNAME:CET
    DTSTART:19701025T030000
    RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
    END:STANDARD
    END:VTIMEZONE
    BEGIN:VTIMEZONE
    TZID:Asia/Kabul
    X-LIC-LOCATION:Asia/Kabul
    BEGIN:STANDARD
    TZOFFSETFROM:+0430
    TZOFFSETTO:+0430
    TZNAME:+0430
    DTSTART:19700101T000000
    END:STANDARD
    END:VTIMEZONE
    BEGIN:VEVENT
    DTSTART;TZID=Europe/Rome:{$yearmonth}23T104500
    DTEND;TZID=Europe/Rome:{$yearmonth}23T114500
    RRULE:FREQ=MONTHLY;BYDAY=-1WE
    DTSTAMP:20220428T082436Z
    UID:4jjnhfc0i0rjri41dkg7g9tk2c@google.com
    CREATED:{$yearmonth}08T143919Z
    DESCRIPTION:<br><br><br><br>Venue:&nbsp\;<br><br><br><br>KabulAfghanistan<b
     r><br><br><br>Contacts:&nbsp\;<br><br><p>PSEA Coordinator: Janet Omogi&nbsp
     \;&nbsp\; &nbsp\; (WFP) &nbsp\;&nbsp\; &nbsp\;<a href="mailto:janet.omogi@w
     fp.org">janet.omogi@wfp.org</a>&nbsp\;</p>
    LAST-MODIFIED:{$yearmonth}08T143919Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:PSEA (Protection from Sexual Exploitation and Abuse) Task Force Mee
     ting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}22T093000
    DTEND;TZID=Asia/Kabul:{$yearmonth}22T103000
    RRULE:FREQ=MONTHLY;BYDAY=-1TU
    DTSTAMP:20220428T082436Z
    UID:4upg1naol4mn8km2jj5mjmcn8a@google.com
    CREATED:{$yearmonth}08T143605Z
    DESCRIPTION:<br><br><br><br><br><br><br>KabulAfghanistan<br><br><br><br>Con
     tacts:&nbsp\;<br><br><p>Cluster Coordinator: François Bellet (UNICEF)&nbsp\
     ;<a href="mailto:fbellet@unicef.org">fbellet@unicef.org</a><br>Co-lead: Jos
     eph Waithaka (DACAAR)&nbsp\;<a href="mailto:joseph.waithaka@dacaar.org">jos
     eph.waithaka@dacaar.org</a></p>
    LAST-MODIFIED:{$yearmonth}08T143820Z
    LOCATION:
    SEQUENCE:1
    STATUS:CONFIRMED
    SUMMARY:WASH Cluster Monthly Meeting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}22T100000
    DTEND;TZID=Asia/Kabul:{$yearmonth}22T110000
    RRULE:FREQ=MONTHLY;BYDAY=-1TU
    DTSTAMP:20220428T082436Z
    UID:3578g74jufefqhv10c09bi5db1@google.com
    CREATED:{$yearmonth}08T143801Z
    DESCRIPTION:<br><br><br><br>Venue:&nbsp\;<br><br><br><br>KabulAfghanistan<b
     r><br><br><br>Contacts:&nbsp\;<br><br><p>GBV AoR Coordinator: Elisa Cappell
     etti (UNFPA)&nbsp\;<a href="mailto:cappelletti@unfpa.org">cappelletti@unfpa
     .org</a><br>GBV AoR co-lead: Terry Alovi (IRC)&nbsp\;<a href="mailto:terry.
     alovi@rescue.org">terry.alovi@rescue.org</a></p>
    LAST-MODIFIED:{$yearmonth}08T143801Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:GBV (Gender Based Violence) Cluster Meeting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}21T140000
    DTEND;TZID=Asia/Kabul:{$yearmonth}21T150000
    RRULE:FREQ=MONTHLY;UNTIL=20221231T192959Z;BYDAY=3MO
    DTSTAMP:20220428T082436Z
    UID:6makgu6k7h86jkutc3bdd66rv8@google.com
    CREATED:{$yearmonth}08T143311Z
    DESCRIPTION:<br><br><br><br>Venue:&nbsp\;<br><br><br><br>KabulAfghanistan<b
     r><br><br><br><br>Contacts:&nbsp\;<br><br><p>WG Co-lead:&nbsp\;&nbsp\; &nbs
     p\;Marie Sophie Pettersson (UN Women)&nbsp\;&nbsp\; &nbsp\;&nbsp\;<a href="
     mailto:marie.pettersson@unwomen.org">marie.pettersson@unwomen.org</a><br>WG
      Co-lead:&nbsp\;&nbsp\; &nbsp\;Zuhra Wardak (IRC)&nbsp\;<a href="mailto:zuh
     ra.wardak@rescue.org">zuhra.wardak@rescue.org</a>&nbsp\;</p>
    LAST-MODIFIED:{$yearmonth}08T143311Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:GiHA (Gender in Humanitarian Action) Monthly Meeting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}16T100000
    DTEND;TZID=Asia/Kabul:{$yearmonth}16T110000
    RRULE:FREQ=MONTHLY;UNTIL=20221231T192959Z;BYDAY=3WE
    DTSTAMP:20220428T082436Z
    UID:2dlhssr48vmdiuo6d32m10am99@google.com
    CREATED:{$yearmonth}08T143111Z
    DESCRIPTION:<br><br><br><br>Venue:&nbsp\;<br><br><br><br>KabulAfghanistan<b
     r><br><br><br><br>Contacts:&nbsp\;<br><br><p>Cluster Coordinator: Daniel Ml
     enga (FAO)&nbsp\;<a href="mailto:daniel.mlenga@fao.org">daniel.mlenga@fao.o
     rg</a></p>
    LAST-MODIFIED:{$yearmonth}08T143111Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:FSAC Cluster Monthly Meeting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}15T103000
    DTEND;TZID=Asia/Kabul:{$yearmonth}15T113000
    RRULE:FREQ=MONTHLY;UNTIL=20211231T192959Z;BYMONTHDAY=15
    DTSTAMP:20220428T082436Z
    UID:578nb58k34q0ajcoffi1rg3bn7@google.com
    CREATED:{$yearmonth}08T142926Z
    DESCRIPTION:<br><br><br><br>Venue:&nbsp\;<br><br><br><br><br>KabulAfghanist
     an<br><br><br><br><br>Contacts:&nbsp\;<br><br><p>MA AoR Coordinator: Matyas
      Juhasz (UNMAS)&nbsp\;<a href="mailto:matyasj@unops.org">matyasj@unops.org<
     /a><br>MA AoR co-lead: Ajmal Ahmadzai (MACCA)&nbsp\;<a href="mailto:ajmal.a
     hmadzai@macca.org.af">ajmal.ahmadzai@macca.org.af</a></p>
    LAST-MODIFIED:{$yearmonth}08T142926Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:Mine Action Monthly Meeting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}14T140000
    DTEND;TZID=Asia/Kabul:{$yearmonth}14T143000
    RRULE:FREQ=MONTHLY;UNTIL=20221231T192959Z;BYDAY=2MO
    DTSTAMP:20220428T082436Z
    UID:4plv9k0ka5dgc4gc3ladtr81na@google.com
    CREATED:{$yearmonth}08T142608Z
    DESCRIPTION:<br><br><br><br>Venue:&nbsp\;<br><br><br><br>KabulAfghanistan<b
     r><br><br><br><br>Contacts:&nbsp\;<br><br><p>AAP Advisor:&nbsp\;&nbsp\; &nb
     sp\;Carolyn Davis (OCHA)&nbsp\;<a href="mailto:carolyn.davis@un.org">caroly
     n.davis@un.org</a>&nbsp\;</p>
    LAST-MODIFIED:{$yearmonth}08T142719Z
    LOCATION:
    SEQUENCE:1
    STATUS:CONFIRMED
    SUMMARY:Accountability to Affected People (AAP) Monthly Meeting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}09T110000
    DTEND;TZID=Asia/Kabul:{$yearmonth}09T113000
    RRULE:FREQ=WEEKLY;WKST=SU;UNTIL=20221231T192959Z;INTERVAL=2;BYDAY=WE
    DTSTAMP:20220428T082436Z
    UID:6ao9ff6esod4e9mv52l9kfsca6@google.com
    CREATED:{$yearmonth}08T142215Z
    DESCRIPTION:
    LAST-MODIFIED:{$yearmonth}08T142228Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:Logistic WG Bi-Weekly Meeting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}08T100000
    DTEND;TZID=Asia/Kabul:{$yearmonth}08T103000
    RRULE:FREQ=WEEKLY;WKST=SU;UNTIL=20221231T192959Z;INTERVAL=2;BYDAY=TU
    DTSTAMP:20220428T082436Z
    UID:471op95t58uigmrem6rko618pf@google.com
    CREATED:{$yearmonth}08T141826Z
    DESCRIPTION:<br><br><br><br>Venue:&nbsp\;<br><br>Afghanistan<br><br><br><br
     >Contacts:&nbsp\;<br><br><p>Cluster Coordinator: Jamshed Tanoli (WHO)&nbsp\
     ;<a href="mailto:tanolij@who.int">tanolij@who.int</a></p>
    LAST-MODIFIED:{$yearmonth}08T141826Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:Health Cluster Bi-Weekly Meeting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}06T100000
    DTEND;TZID=Asia/Kabul:{$yearmonth}06T110000
    RRULE:FREQ=MONTHLY;UNTIL=20221031T192959Z;BYDAY=1SU
    DTSTAMP:20220428T082436Z
    UID:287j5uks6rhnpiradbv6buukhh@google.com
    CREATED:{$yearmonth}08T141614Z
    DESCRIPTION:<br><br><br><br>Venue:&nbsp\;<br><br><br><br><br>KabulAfghanist
     an<br><br><br><br><br>Contacts:&nbsp\;<br><br><p>WG Coordinator: Cleopatra
     Chipuriro (UNICEF)&nbsp\;<a href="mailto:cchipuriro@unicef.org">cchipuriro@
     unicef.org</a><br>WG Co-lead:&nbsp\;&nbsp\; &nbsp\;Najeebullah Qadri&nbsp\;
     &nbsp\; &nbsp\;(SCI)&nbsp\;<a href="mailto:najeebullah.qadri@savethechildre
     n.org">najeebullah.qadri@savethechildren.org</a>&nbsp\;</p>
    LAST-MODIFIED:{$yearmonth}08T141614Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:Education in Emergencies (EiE) WG Monthly Meeting
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART:{$yearmonth}03T053000Z
    DTEND:{$yearmonth}03T063000Z
    DTSTAMP:20220428T082436Z
    UID:4t5bm1vviu17lc9lcl874uq109@google.com
    CREATED:{$yearmonth}08T141344Z
    DESCRIPTION:Training\n\n\nInternational Organization for Migration\nUnited
     Nations High Commissioner for Refugees
    LAST-MODIFIED:{$yearmonth}08T141344Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:ESNFI Cluster Reporthub Online Training
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;TZID=Asia/Kabul:{$yearmonth}02T100000
    DTEND;TZID=Asia/Kabul:{$yearmonth}02T110000
    RRULE:FREQ=MONTHLY;UNTIL=20221231T192959Z;BYDAY=1WE
    DTSTAMP:20220428T082436Z
    UID:42m164v9jbtg3rigefq9lvmnqo@google.com
    CREATED:{$yearmonth}08T140754Z
    DESCRIPTION:CP AoR co-lead: Randi Saure (Save the Children)&nbsp\;<a href="
     mailto:cpaor.afghanistan@gmail.com">cpaor.afghanistan@gmail.com</a><br>CP A
     oR co-lead: Elizabeth Njoki Muthama (UNICEF)&nbsp\;<a href="mailto:cpaor.af
     ghanistan@gmail.com" id="ow577" __is_owner="true">cpaor.afghanistan@gmail.c
     om</a>
    LAST-MODIFIED:{$yearmonth}08T141102Z
    LOCATION:Kabul\, Afghanistan
    SEQUENCE:1
    STATUS:CONFIRMED
    SUMMARY:Child Protection Cluster meeting
    TRANSP:TRANSPARENT
    END:VEVENT
    BEGIN:VEVENT
    DTSTART:{$yearmonth}08T141500Z
    DTEND:{$yearmonth}08T151500Z
    DTSTAMP:20220428T082436Z
    UID:30mfi1vrvup0sjbe3ie5t26jge@google.com
    CREATED:{$yearmonth}08T122555Z
    DESCRIPTION:
    LAST-MODIFIED:{$yearmonth}08T122600Z
    LOCATION:
    SEQUENCE:1
    STATUS:CONFIRMED
    SUMMARY:Test by Andrej: only Change to Events access
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART;VALUE=DATE:{$yearmonth}10
    DTEND;VALUE=DATE:{$yearmonth}11
    DTSTAMP:20220428T082436Z
    UID:615tenghf156kul10a57a95va0@google.com
    CREATED:{$yearmonth}02T144252Z
    DESCRIPTION:
    LAST-MODIFIED:{$yearmonth}02T144252Z
    LOCATION:
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:Test color red
    TRANSP:TRANSPARENT
    END:VEVENT
    BEGIN:VEVENT
    DTSTART:20220128T160000Z
    DTEND:20220128T170000Z
    DTSTAMP:20220428T082436Z
    UID:1iq49iadt85jkbrc4oh1cjotvi@google.com
    CREATED:20220128T142635Z
    DESCRIPTION:Gonna get together and dig the deepest well ever known to man k
     ind! But\, only with shovels.
    LAST-MODIFIED:20220128T142635Z
    LOCATION:Kandahar\, Afghanistan
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:WASH: Dig a well!
    TRANSP:OPAQUE
    END:VEVENT
    BEGIN:VEVENT
    DTSTART:20220128T150000Z
    DTEND:20220128T160000Z
    DTSTAMP:20220428T082436Z
    UID:0gd8klehejv55d35u719606ir2@google.com
    CREATED:20220128T142538Z
    DESCRIPTION:Add some descriptions &amp\; <b>formated</b> <u>text</u>&nbsp\;
     Maybe even a link to a <a href="https://www.humanitarianresponse.info/" id=
     "ow2144" __is_owner="true">weird website</a>.
    LAST-MODIFIED:20220128T142538Z
    LOCATION:Kabul University\, Kabul\, Afghanistan
    SEQUENCE:0
    STATUS:CONFIRMED
    SUMMARY:Health: Test event 1
    TRANSP:OPAQUE
    ATTACH;FILENAME=AWS_costs_by_projct_for_past_6_months.csv;FMTTYPE=text/csv:
     https://drive.google.com/open?id=1cCMFiW214cBcrOc9dvTVXg7SHIltiIGz&authuser
     =1
    ATTACH;FILENAME=PakResponse - Infection_detected - 17-August-2021.png;FMTTY
     PE=image/png:https://drive.google.com/open?id=1potvMnJLaKqlgBZUqatex7IaQ0hK
     U6Oh&authuser=1
    END:VEVENT
    END:VCALENDAR
ICAL;
  }

  /**
   * Test data 1.
   */
  private function getTestIcal2() {
    $yearmonth = date('Ym');

    return <<<ICAL
    This will not parse
ICAL;
  }
}
