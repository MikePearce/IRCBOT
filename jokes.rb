require 'rubygems'
require 'mechanize'

a = Mechanize.new { |agent|
    agent.user_agent_alias = 'Mac Safari'
}

#See if there's a query
if (ARGV[0]) then
    url = "http://www.onelinerz.net/search-one-liners/?q=" + ARGV[0]
else
    url = "http://www.onelinerz.net/top-100-funny-one-liners/#{rand(5)}" 
end

page = a.get(url)
jokes = page.parser.xpath('//*[@class="oneliner"]') 

if (jokes.size > 0) then
    puts jokes[rand(jokes.size)].inner_html
end
