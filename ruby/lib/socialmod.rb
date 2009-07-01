require 'rubygems'
require 'activeresource'

module Socialmod
  class Base < ActiveResource::Base
    self.site = "http://api.socialmod.com"
    self.timeout = 5
    
    cattr_accessor :headers; @@headers = {}
    cattr_accessor :api_key
  
    class << self
      def api_key=(key)
        self.headers['Authorization'] = key
        @@api_key = key
      end
      
      def callback_url=(url)
        @@callback_url = url
      end
      
      def callback_url
        @@callback_url ||= nil
      end
    end
  
    def initialize(*args)
      super(*args)
      if Base.callback_url
        attributes["callback_url"] ||= Base.callback_url
      end
      if respond_to?(:after_initialize)
        send(:after_initialize)
      end
    end
  end
end

$: << File.dirname(__FILE__)
require 'socialmod/dashboard'
require 'socialmod/user'
require 'socialmod/item'
require 'socialmod/callback'