require 'rubygems'
require 'activeresource'

module SocialMod
  class Base < ActiveResource::Base
    self.site = "http://api.socialmod.com"
    self.timeout = 5
    
    cattr_accessor :headers; @@headers = {}
  
    class << self
      def api_key=(key)
        self.headers['Authorization'] = key
      end
    end
  
    def initialize(*args)
      super(*args)
      if respond_to?(:after_initialize)
        send(:after_initialize)
      end
    end
  end
end

$: << File.dirname(__FILE__)
require 'socialmod/dashboard'
require 'socialmod/user'
require 'socialmod/moderate'