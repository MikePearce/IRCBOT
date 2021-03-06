<?php

include_once('Net/SmartIRC.php');
include_once('settings.php');
include_once('thimblBot.php');

// Instantiate
$bot = new PrimeBot();
$bot->setBotName('AgileBot');
$bot->setBotMaster('MikePearce');
$bot->setIdentifyPassword($identifyPassword);
$bot->setDbDetails($db_database, $db_user, $db_password);
$bot->setMaxChannels(5);
$irc = new Net_SmartIRC();

//$irc->setDebug(SMARTIRC_DEBUG_IRCMESSAGES);
$irc->setUseSockets(TRUE);

// Set a global handler for joins and messages
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL|SMARTIRC_TYPE_QUERY, '.*', $bot, 'actionHandler');
$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'joinHandler');
$irc->registerActionhandler(SMARTIRC_TYPE_PART, '.*', $bot, 'partHandler');
$irc->connect('irc.affiliatewindow.com', 6667);


$channel = array();
foreach($argv AS $arg)
{
    if (strpos($arg, '.php') === false) {
        $channel[] = '#'.$arg;    
    }
}
$bot->setChannels($channel);
$irc->login($bot->getBotName(), $bot->getBotName(), 8,$bot->getBotName());
$irc->join($channel);
$irc->listen();
$irc->disconnect();

