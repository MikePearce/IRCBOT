require 'rubygems'
  require 'mechanize'

  a = Mechanize.new { |agent|
    agent.user_agent_alias = 'Mac Safari'
  }

  func = ARGV[0] ? ARGV[0] : ''
  page = a.get('http://uk2.php.net/' + func)
  puts page.parser.xpath("//div[@class='methodsynopsis dc-description']")

