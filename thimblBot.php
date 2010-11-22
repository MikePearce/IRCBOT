<?php

include_once('Net/SmartIRC.php');

class PrimeBot {
    
    private $_yql = "http://query.yahooapis.com/v1/public/yql?q=";
    
    private $_version = '2.3';
    
    private $_botName;
    
    private $_options;
    
    private $_data;
    
    private $_irc;
    
    private $_actionHandlers;
    
    private $_joinHandlers;
    
    private $_partHandlers;

    private $_ticketPassword;
    
    /**
     * 
     * Setup some stuff
     */
    public function __construct()
    {
        $this->_botName = 'PrimeBot';
        
        // Setup some options for connecting remotely
        $this->_options = array( 
        	'http' => array(
        		'user_agent'    => 'IRC Question Bot',
        		'max_redirects' => 10,              
        		'timeout'       => 120,         
            ) 
        );
        
        $this->_actionHandlers = array(
            '^!version' => 'version',
            '^!8ball' => '8ball',
            '^!joke' => 'joke',
            '^!usage' => 'usage',
            '^[hello|hi] '. $this->_botName  => 'helloBot',
            '^!lolcat' => 'getLolcat',
            '^!help'	=> 'botHelp',
            '^!seen' => 'seen',
            '^!channelstats' => 'channelStats',
            '^!warez' => 'warez',
            '^!question' => 'askQuestion',
            '^!brofist' => 'broFist',
            '^!tickets' => 'tickets',
            $this->_botName  => 'hello',
            //'^!.*' => 'shakesHead',
        );
        
        $this->_joinHandlers = array(
            //'.*'	=> 'hello',
        );
        $this->_partHandlers = array(
        );        
        
    }
    
    /**
     * Get the bot name
     */
    public function getBotName()
    {
        return $this->_botName;
    }
    
    /**
     * Set the bot name
     */
    public function setBotName($b)
    {
        $this->_botName = $b;
    }

    /**
     * Set ticket pass
     */
    public function setTicketPassword($p)
    {
        $this->_ticketPassword = $p;
    }

    /**
     * Get ticket pass
     */
    public function getTicketPassword()
    {
        return $this->_ticketPassword;
    }
    
    /**
     * 
     * The handler of actions!
     * 
     * @param object $irc
     * @param object $data
     */
    public function actionHandler($irc, $data)
    {
        $this->_irc = $irc;
        $this->_data = $data;
        
        foreach($this->_actionHandlers AS $regex => $method)
        {
            if (preg_match('/'. $regex .'/', $this->_data->message))
            {
                $method = "_". $method;
                $this->{$method}();
                
                break;
            }
        }
        
        // Log...
        $this->_log();
    }
    
    /**
     * What do we do on Join?
     * @param object $irc
     * @param object $data
     */
    public function joinHandler($irc, $data)
    {
        $this->_irc = $irc;
        $this->_data = $data;
        
        foreach($this->_joinHandlers AS $regex => $method)
        {
            if (preg_match('/'. $regex .'/', $this->_data->message))
            {
                $method = "_". $method;
                $this->{$method}();
            }
        }
        
        // Log...
        $this->_joinLog();
        
    }
    
    /**
     * What do we do on Part?
     * @param object $irc
     * @param object $data
     */
    public function partHandler($irc, $data)
    {
        $this->_irc = $irc;
        $this->_data = $data;
        
        foreach($this->_partHandlers AS $regex => $method)
        {
            if (preg_match('/'. $regex .'/', $this->_data->message))
            {
                $method = "_". $method;
                $this->{$method}();
            }
        }
        
        // Log...
        $this->_partLog();
        
    }   
    
    /**
     * Make DB connection
     * 
     * @return PDO
     */
    private function _connectToDb()
    {
        return  new PDO("mysql:host=localhost;dbname=bot_logger", 'root', '');
    }

    /**
     * Log the user leaving
     */
    private function _partLog()
    {
        try {
            // Connect
            $dbh = $this->_connectToDb();
            
            // Insert
            $dbh->exec("INSERT INTO log_table(`nick`, `said`, `when`) VALUES ('". $this->_data->nick ."','#part#',NOW())");
            
            $dbh = null;
        	
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
    }
    
    /**
     * Log when the user joined
     */
    private function _joinLog()
    {
        try {
            // Connect
            $dbh = $this->_connectToDb();
            
            // Insert
            $dbh->exec("INSERT INTO log_table(`nick`, `said`, `when`, `channel`) VALUES ('". $this->_data->nick ."','#join#',NOW(), '". $this->_data->channel ."')");
            
            $dbh = null;
        	
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
    }
    
    

    /**
     * Log what the user says
     */
    private function _log()
    {
        try {
            // Connect
            $dbh = $this->_connectToDb();
            
            // Insert
            $stmt = $dbh->prepare("INSERT INTO log_table(`nick`, `said`, `when`, `channel`) VALUES (:nick, :message, NOW(), :channel)");
            
            $stmt->bindParam(':nick', $this->_data->nick, PDO::PARAM_STR);
            $stmt->bindParam(':message', $this->_data->message, PDO::PARAM_STR);
            $stmt->bindParam(':channel', $this->_data->channel, PDO::PARAM_STR);
            
            $stmt->execute();
            
            $dbh = null;
        	
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * Grab assigned tickets
     * @todo Make a bit cleverer, get new|review|testing etc
     */
    private function _tickets()
    {
        $user = str_replace("!tickets ", "", $this->_data->message);
        $url = 'http://'. $this->getTicketPassword() .'@dtrac.affiliatewindow.com/query?'.
            'status=assigned&'.
            'status=deploy&'.
            'status=development_done&'.
            'status=new&'.
            'status=review&'.
            'status=testing&'.
            'status=testing_done&'.
            'format=rss&'.
            'order=priority&'.
            'owner='. $user;

        $xml = simplexml_load_file(urlencode($url));
        $tickets = false;
        foreach($xml->channel->item AS $item)
        {
            if ($item->title !== '') {
                $this->_privmessage(
                    $this->_data->nick .": ". $item->title .": (". $item->link .")",
                    $this->_data->nick
                );
            }
            $tickets = true;
        }
        if (!$tickets) {
            $this->_privmessage(
                $this->_data->nick .": You have no tickets assigned!",
                $this->_data->nick
            );
        }
    }
    
    /**
	 * Throw a bro fist
     */
    private function _broFist()
    {
        $this->_message("..............__");
        $this->_message("......../´¯/''/´`¸");
        $this->_message("...../'/../../..../¨¯\\");
        $this->_message("...('(...´..´.¯~/'..')");
        $this->_message("....\...........'.../");
        $this->_message(".....\...\.... _.·´");
        $this->_message("......\.......(");
        $this->_message("BRO FIST");
    }
    
    private function _shakesHead()
    {
        $this->_privmessage('Sorry, that doesn\'t do anything!', $this->_data->nick);
    }
    
    /**
     * Useless
     */
    private function _warez()
    {
        $this->_message($this->_data->nick.': No warez. This is a respectable bot!');
    }
    
    /**
     * Provide some basic channel stats
     */
    private function _channelStats()
    {
        $dbh = $this->_connectToDb();
        
        $stmt = $dbh->query("select nick, count(*) as counter FROM log_table WHERE channel = '". $this->_data->channel ."' GROUP BY nick;");
        
        $this->_message($this->_data->nick .': stats for '. $this->_data->channel .':');
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
        foreach ($rows AS $row) {
            $this->_message($row->nick .' has '. $row->counter .' lines of babble.');    
        }
                
    }
    
    /**
     * When did we last see someone?
     */
    private function _seen()
    {
        $nick = str_replace(array("!seen ", "!seen"), "", $this->_data->message);
        
        if (trim($nick) == '')
            return;
            
        $dbh = $this->_connectToDb();
        
        $stmt = $dbh->query("SELECT * FROM log_table WHERE channel = '". $this->_data->channel ."' AND LOWER(nick) = '". strtolower(trim($nick)) ."' ORDER BY id DESC LIMIT 1");
        
        $row = $stmt->fetch(PDO::FETCH_OBJ);

        if (isset($row->when)) {
            $this->_message($this->_data->nick.': I last saw '. trim($nick) .' at '. $row->when .' saying "'. trim($row->said) .'"');    
        }
        else {
            $this->_message($this->_data->nick.': I\'ve never seen '. trim($nick));
        }
        
                
    }
    /**
     * Receives question and tries to answer with yahoo answers
     */
    private function _askQuestion()
    {
        // Grab the question
        $question = str_replace("!question ", "", $this->_data->message);

        // Create the yql
        $yql = 'select * from answers.search where query="'. $question .'" and type="resolved"';
    
        $array = $this->grabData($yql);
        
        $stuff = $array['query']['results']['Question'];
        if (is_array($stuff)) {
            $answer = $stuff[array_rand($stuff)]['ChosenAnswer'];
            $link = $stuff[array_rand($stuff)]['Link'];
        }
        $this->_message($this->_data->nick.': You asked: '. $question);
        if (!$answer) {
            $this->_message($this->_data->nick.': Sorry, I cannot answer that one! Try asking a simpler question.');    
        }
        else {
            $this->_message($this->_data->nick.': Yahoo! answers says: '. $answer .' ('. $link .')');
        }
    }
    
    /**
     * Return functions
     */
    private function _botHelp()
    {
        $this->_privmessage('Help eh? These are the things I can do:', $this->_data->nick);
        foreach( array(
        	'!usage <php function> - this will grab the usage for the function',
            '!lolcat - I will return the URL for a random lolcat image from flickr',
        	'!joke <theme (optional)>- Ask me to tell you a joke, supply an optional theme!',
        	'!question <short question> - I will attempt to answer the question!',
        	'!8ball <question> - I will use my magic 8 ball to answer the question',
            '!version - Learn a bit about me.',
            '!seen <nick> - I can tell you the last time I saw a user',
            '!warez <searchterm> - I will list all my 0day warez I can file transfer you!',
            '!channelstats - Will return some basic channel stats',
            '!tickets <user> - Will return any dtrac tickets assigned to that user',
            '!messagefor-<nick> message goes here - leave a message for someone (not implemented yet)'
        ) AS $thing) {
            $this->_privmessage($thing, $this->_data->nick);
        }
        
    }    
    
    /**
     * Wholly useless function to get lolcat URL from flickr
     */
    private function _getLolcat()
    {
        $query = 'select id, title from flickr.photos.search(50) where tags="lolcat" or text="lolcat"';

        $array = $this->grabData($query);
        $stuff = $array['query']['results']['photo'];
        
        $id = $stuff[array_rand($stuff)]['id'];
        $title = $stuff[array_rand($stuff)]['title'];
        
        $sizes = $this->grabData('select source from flickr.photos.sizes where photo_id = '. $id);
        
        $this->_message($this->_data->nick.': '. $title .' => '. $sizes['query']['results']['size'][3]['source']);
        
    }    
    
    /**
     * Respond to niceties
     */
    private function _helloBot()
    {
       // if _we_ join, don't greet ourself, just jump out via return
        if ($this->_data->nick == $this->_irc->_nick)
            return;
        
        // it is, lets greet the joint user
        $this->_message('Hi '.$this->_data->nick);
    }    
    
    /**
     * 
     * Greet the user and update last seen stats
     */
    private function _hello()
    {
       // if _we_ join, don't greet ourself, just jump out via return
        if ($this->_data->nick == $this->_irc->_nick)
            return;
        
        $greetings = array(
            'Mirëdita' => 'Albanian friends',
            'Ahalan' => 'Arabic',
            'Parev' => 'Armenian',
            'Zdravei' => 'Bulgarian',
            'Nei Ho' => 'Chinese',
            'Dobrý den' => 'Czech',
            'Goddag' => 'Danish',
            'Goede dag' => 'Dutch',
            'Hello' => 'English',
            'Saluton' => 'Esperanto',
            'Hei' => 'Finnish',
            'Bonjour' => 'French',
            'Guten Tag' => 'German',
            'Gia\'sou' => 'Greek',
            'Aloha' => 'Hawaiian',
            'Shalom' => 'Hebrew',
            'Namaste' => 'Hindi',
            'Jó napot' => 'Hungarian',
            'Góðan daginn' => 'Icelandic',
            'Halo' => 'Indonesian',
            'Aksunai Qanuipit?' => ' Inuit',
            'Dia dhuit' => 'Irish',
            'Salve' => 'Italian',
            'Kon-nichiwa' => 'Japanese',
            'An-nyong Ha-se-yo' => ' Korean',
            'Salvëte' => 'Latin',
            'Ni hao' => 'Mandarin',
            'Hallo' => 'Norwegian',
            'Dzien\' dobry' => 'Polish',
            'Olá' => 'Portuguese',
            'Bunã ziua' => 'Romanian',
            'Zdravstvuyte' => 'Russian',
            'Hola' => 'Spanish',
            'Hujambo' => ' Swahili',
            'Hej' => 'Swedish',
            'Sa-wat-dee' => 'Thai',
            'Merhaba' => 'Turkish',
            'Vitayu' => 'Ukrainian',
            'Xin chào' => 'Vietnamese',
            'Hylo; Sut Mae?' => 'Welsh',
            'Sholem Aleychem' => 'Yiddish',
            'Sawubona' => 'Zulu');
        
        $greeting = array_rand($greetings);
        $language = $greetings[$greeting];
        $this->_privmessage($greeting .' '. $this->_data->nick .'! (That\'s how you greet someone in '. $language .')');
        $this->_privmessage('Type "!help" to find out what I can do for you.', $this->_data->nick);
    }    
    
    /**
     * Scrape usage instructions from php.net
     */
    private function _usage()
    {
        $question = str_replace("!usage ", "", $this->_data->message);
        
        $question = escapeshellcmd($question);
        $hack = false;
        if (strpos($question, ";") === FALSE ) {
            $value = `ruby phpManual.rb $question`;
            
        }
        else {
            $hack = true;    
        }
        
        $result = str_replace("\n", "", strip_tags($value));
        
        if ($value) {
            $this->_message($this->_data->nick.': '. $result);
        }
        else {
            $this->_message($this->_data->nick.': Sorry, cannot find that function ('. $question .')');
        }
        
    }    

    /**
     * Scrape a joke from oneliners.net
     */
    private function _joke()
    {
        $question = str_replace(array("!joke ", "!joke"), "", $this->_data->message);
        $question = escapeshellcmd($question);
        
        if ($question == "") {
            $value = `ruby jokes.rb`;
        }
        else {
            $value = `ruby jokes.rb $question`;    
        }
        
        if ($value) {
            $this->_message($this->_data->nick.': '. $value);    
        }
        else {
            $this->_message($this->_data->nick.': No jokes about '. $question .'');
   
        }
        
    }    
    
    /**
     * Magic 8ball function!
     */
    private function _8ball()
    {
        $eight_ball = array('As I see it, yes
            ', 'It is certain
            ', 'It is decidedly so
            ', 'Most likely
            ', 'Outlook good
            ', 'Signs point to yes
            ', 'Without a doubt
            ', 'Yes
            ', 'Yes – definitely
            ', 'You may rely on it
            ', 'Reply hazy, try again
            ', 'Ask again later
            ', 'Better not tell you now
            ', 'Cannot predict now
            ', 'Concentrate and ask again
            ', 'Don\'t count on it
            ', 'My reply is no
            ', 'My sources say no
            ', 'Outlook not so good
            ', 'Very doubtful'
        );
        
        $this->_message(
            $this->_data->nick .': 8 Ball Says: '. $eight_ball[array_rand($eight_ball)]
        );
    }
    
    /**
     * Return version
     */
    private function _version() 
    {   
        $this->_message($this->_data->nick .': Version v'. $this->_version .' authored by Mike Pearce.');
    }
    
    /**
     * Print message to current channel
     * @param string $message
     */
    private function _message($message)
    {
        $this->_irc->message(SMARTIRC_TYPE_CHANNEL, $this->_data->channel, $message);
    }
    
    /**
     * Print message to current channel
     * @param string $message
     */
    private function _privmessage($message, $user)
    {
        $this->_irc->message(SMARTIRC_TYPE_QUERY, $user, $message);
    }    
    
    private function _notice($message, $user)
    {
        return;
        $this->_irc->message(SMARTIRC_TYPE_CHANNEL, $user, $message);
    }
    
    /**
     * 
     * Connect to the YQL service and grab the data
     * 
     * @param string $yql
     * @param string $format
     */
    private function grabData($yql, $format = 'json')
    {
        //Build the URL
        $url = $this->_yql . urlencode($yql) .'&format='. $format;
        
        // Grab the json
        $options = array( 
        	'http' => array(
        		'user_agent'    => 'IRC Question Bot',
        		'max_redirects' => 10,              
        		'timeout'       => 120,         
            ) 
        );
        $context = stream_context_create( $options );
        $page    = @file_get_contents( $url, false, $context );
        if ($format == 'json') {
            $ret = json_decode($page, true);    
        }
        else {
            $ret = simplexml_load_string($page);
        }
        
        
        return $ret;
    }
    
}