require 'rubygems'
require 'activeresource'

class Moderate < ActiveResource::Base
  self.site = "http://api.socialmod.com"
  self.timeout = 5
    
  # This is a hack to get inheritance working
  cattr_accessor :headers; @@headers = {}
  cattr_accessor :element_name; @@element_name = 'item'
  cattr_accessor :collection_name; @@collection_name = 'items'
  
  def initialize(*args)
    super(*args)
    if respond_to?(:after_initialize)
      send(:after_initialize)
    end
  end
  
  # Have a look at the API docs for
  # information on attributes
  
  # # Schedule moderation
  # v = Moderate::Video.new
  # v.src = 'http://example.com/test.flv'
  # v.custom_id = 3384
  # v.save!
  #
  # # Find moderated items
  # task :moderate => [:environment] do 
  #   Moderate.find_moderated.each do |mod|
  #     vid = FooVideo.find_by_custom_id(mod.custom_id) rescue next
  #     vid.passed_moderation = mod.passed?
  #     vid.save(false)
  #   end
  # end
  # 
  # # Flag moderate item
  # item = Moderate.find_by_custom_id(mod.custom_id)
  # item.flag!
  
  class << self
    def api_key=(key)
      self.headers['Authorization'] = key
    end
    
    # Find all moderated since last sync
    def sync
      find(:all, :from => :sync)
    end
    alias :find_moderated :sync
    
    def find_by_custom_id(id)
      find(:one, :from => :custom, :params => {:id => id})
    end
  end
  
  def flag!
    post(:flag)
  end
  
  def passed?
    verdict == 'passed'
  end
  
  def failed?
    verdict == 'failed' ||
      verdict == 'deferred'
  end
  
  def pending?
    verdict == 'pending'
  end
  
  def destroy
    raise "You can't destroy an item"
  end
    
  def update
    raise "You can't update an item"
  end
  
  class Image < Moderate
    def after_initialize
      self.mime = 'image/jpg'
    end
  end
  
  class Video < Moderate
    def after_initialize
      self.mime = 'video/x-flv'
    end
  end
  
  class Audio < Moderate
    def after_initialize
      self.mime = 'audio/mp3'
    end
  end
  
  class Text < Moderate
    def src=(*a)
      raise "You can't set src for text moderation"
    end
  end
end