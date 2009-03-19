require 'socialmod'

module SocialMod
  class Dashboard < Base
    self.collection_name = 'dashboard'
    
    def self.find(*args)
      super(:one, :from => collection_path)
    end
  end
end