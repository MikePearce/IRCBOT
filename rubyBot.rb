# PrimeBot

#class PrimeBot#
#
#    def initialize
#        @yql = 
#        
#    end
#end

require 'rubygems'
require 'net/yail'

irc = Net::YAIL.new(
  :address    => 'irc.affiliatewindow.com',
  :username   => 'PrimeBot',
  :realname   => 'PrimeBot',
  :nicknames  => ['PrimeBot', 'PrimeDog', 'PrimeCat']
)

irc.prepend_handler :incoming_welcome, proc {
  irc.join('#mike2')
  irc.msg('#mike2', 'Hello!')
  return false
}

def message(text, args)
puts args.inspect
    #@irc.message(
    return false
end

irc.prepend_handler :incoming_message, method(:message)

irc.start_listening
while irc.dead_socket == false
  # Avoid major CPU overuse by taking a very short nap
  sleep 0.05
end

