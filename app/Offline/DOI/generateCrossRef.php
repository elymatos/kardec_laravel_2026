<?php

namespace App\Offline\DOI;

use App\Database\Criteria;

class generateCrossRef
{
    public $xml;

    public function __construct()
    {
        $xmlBase = '<?xml version="1.0" encoding="utf-8"?>
<doi_batch
    xmlns="http://www.crossref.org/schema/4.3.6"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:jats="http://www.ncbi.nlm.nih.gov/JATS1"
    xmlns:ai="http://www.crossref.org/AccessIndicators.xsd"
    version="4.3.6"
    xsi:schemaLocation="http://www.crossref.org/schema/4.3.6 https://www.crossref.org/schemas/crossref4.3.6.xsd">
  <head>
    <doi_batch_id>_1724425192</doi_batch_id>
    <timestamp>1724425192</timestamp>
    <depositor>
      <depositor_name>Adriana</depositor_name>
      <email_address>adriana.oliveira@ufjf.edu.br</email_address>
    </depositor>
    <registrant>UFJF</registrant>
  </head>
  <body>
</body>
</doi_batch>
    ';
        $this->xml = simplexml_load_string($xmlBase);
    }

    // <journal_metadata>
    // <full_title>Lynx</full_title>
    // <abbrev_title>Lynx</abbrev_title>
    // <issn media_type="electronic">2675-4126</issn>
    // </journal_metadata>
    // <journal_issue>
    // <publication_date media_type="online">
    // <month>08</month>
    // <day>05</day>
    // <year>2024</year>
    // </publication_date>
    // <journal_volume>
    // <volume>3</volume>
    // </journal_volume>
    // </journal_issue>
    // <journal_article xmlns:jats="http://www.ncbi.nlm.nih.gov/JATS1" xmlns:ai="http://www.crossref.org/AccessIndicators.xsd" publication_type="full_text" metadata_distribution_opts="any">
    // <titles>
    // <title>Editorial</title>
    // </titles>
    // <contributors>
    // <person_name contributor_role="author" sequence="first" language="pt">
    // <given_name>Liamara</given_name>
    // <surname>Scortegagna</surname>
    // <ORCID>https://orcid.org/0000-0001-6825-4945</ORCID>
    // </person_name>
    // <person_name contributor_role="author" sequence="additional" language="pt">
    // <given_name>Priscila de Faria</given_name>
    // <surname>Pinto</surname>
    // </person_name>
    // </contributors>
    // <jats:abstract xmlns:jats="http://www.ncbi.nlm.nih.gov/JATS1">
    //          <jats:p>Lynx - Editorial do v. 3, 2023: Fluxo contínuo</jats:p>
    //        </jats:abstract>
    //        <publication_date media_type="online">
    //          <month>08</month>
    //          <day>14</day>
    //          <year>2024</year>
    //        </publication_date>
    //        <ai:program xmlns:ai="http://www.crossref.org/AccessIndicators.xsd" name="AccessIndicators">
    //          <ai:license_ref>https://creativecommons.org/licenses/by/4.0</ai:license_ref>
    //        </ai:program>
    //        <doi_data>
    //          <doi>10.34019/2675-4126.2023.v3.45600</doi>
    //          <resource>https://periodicos.ufjf.br/index.php/lynx/article/view/45600</resource>
    //          <collection property="crawler-based"/>
    //          <collection property="text-mining"/>
    //        </doi_data>
    //      </journal_article>
    public function process()
    {
        $time = time();
        $this->xml->head->doi_batch_id = "_{$time}";
        $this->xml->head->timestamp = $time;
        $items = Criteria::table('view_items')
            ->where('idItem', '<', 40)
            ->all();
        foreach ($items as $item) {
            $journal = $this->xml->body->addChild('journal');
            $journal_metadata = $journal->addChild('journal_metadata');
            $full_title = $journal_metadata->addChild('full_title', 'Projeto Allan Kardec');
            $abbrev_title = $journal_metadata->addChild('abbrev_title', 'Projeto Allan Kardec');

            $journal_issue = $journal->addChild('journal_issue');
            $publication_date = $journal_issue->addChild('publication_date');
            $publication_date->addAttribute('media_type', 'online');
            $month = $publication_date->addChild('month', substr($item->dtPublishedOrder, 4, 2));
            $day = $publication_date->addChild('day', substr($item->dtPublishedOrder, 6, 2));
            $year = $publication_date->addChild('year', substr($item->dtPublishedOrder, 0, 4));

            $journal_article = $journal->addChild('journal_article');
            $journal_article->addAttribute('xmlns:xmlns:jats', 'http://www.ncbi.nlm.nih.gov/JATS1');
            $journal_article->addAttribute('xmlns:xmlns:ai', 'http://www.crossref.org/AccessIndicators.xsd');
            $journal_article->addAttribute('publication_type', 'full_text');
            $journal_article->addAttribute('metadata_distribution_opts', 'any');

            $titles = $journal_article->addChild('titles');
            $title = $titles->addChild('title', $item->ptTitle);

            $contributors = $journal_article->addChild('contributors');
            $person_name = $contributors->addChild('person_name');
            $person_name->addAttribute('contributor_role', 'editor');
            $person_name->addAttribute('sequence', 'first');
            $person_name->addAttribute('language', 'pt');
            $given_name = $person_name->addChild('given_name', 'Brutus');
            $surname = $person_name->addChild('surname', 'Abel');

            $publication_date_article = $journal_article->addChild('publication_date');
            $publication_date_article->addAttribute('media_type', 'online');
            $month = $publication_date_article->addChild('month', substr($item->dtPublishedOrder, 4, 2));
            $day = $publication_date_article->addChild('day', substr($item->dtPublishedOrder, 6, 2));
            $year = $publication_date_article->addChild('year', substr($item->dtPublishedOrder, 0, 4));

            $id = substr('000'.$item->idItem, -5);
            $doi_data = $journal_article->addChild('doi_data');
            $doi = $doi_data->addChild('doi', "10.34019/ufjf.PKardec.{$id}");
            $resource = $doi_data->addChild('resource', "https://projetokardec.ufjf.br/item-pt?id={$item->idItem}");

            //        <doi_data>
            //          <doi>10.34019/2675-4126.2023.v3.45600</doi>
            //          <resource>https://periodicos.ufjf.br/index.php/lynx/article/view/45600</resource>
            //          <collection property="crawler-based"/>
            //          <collection property="text-mining"/>
            //        </doi_data>
        }

        debug($this->xml);
        $filename = __DIR__.'/xml2.xml';
        $this->xml->asXML($filename);
    }
}
