<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// squad
$squad = new SimpleXMLElement('<?DOCTYPE squad SYSTEM "squad.dtd"?><?xml-stylesheet href="squad.xsl" type="text/xsl"?><squad/>');
$squad->addAttribute('nick', $group_name);

// info-tags
$squad->addChild('name', 'Swedish Strategic Group');
$squad->addChild('email', 'info@ssg-clan.se');
$squad->addChild('web', 'https://www.ssg-clan.se');
$squad->addChild('picture', "{$group_code}.paa"); // ligger i <root>/xml-mappen
$squad->addChild('title', 'SSG');

// members i squad
foreach($members as $member)
{
	$xml_member = $squad->addChild('member');
	$xml_member->addAttribute('id', $member->uid);
	$xml_member->addAttribute('nick', "SSG {$member->name}");

	// member child elements
	$xml_member->addChild('name', $member->name);
	$xml_member->addChild('email', null);
	$xml_member->addChild('icq', null);
	$xml_member->addChild('remark', $group_name);
}

// print
Header('Content-type: text/xml');
echo $squad->asXML();