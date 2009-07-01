# -*- encoding: utf-8 -*-

Gem::Specification.new do |s|
  s.name = %q{socialmod}
  s.version = "0.2.0"

  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.authors = ["Alex MacCaw"]
  s.date = %q{2009-07-01}
  s.description = %q{Ruby libraries for Socialmod moderation service}
  s.email = %q{alex@socialmod.com}
  s.extra_rdoc_files = [
    "README"
  ]
  s.files = [
    "lib/socialmod.rb",
     "lib/socialmod/callback.rb",
     "lib/socialmod/dashboard.rb",
     "lib/socialmod/item.rb",
     "lib/socialmod/user.rb"
  ]
  s.homepage = %q{http://github.com/maccman/socialmod}
  s.rdoc_options = ["--charset=UTF-8"]
  s.require_paths = ["lib"]
  s.rubygems_version = %q{1.3.4}
  s.summary = %q{Socialmod library}

  if s.respond_to? :specification_version then
    current_version = Gem::Specification::CURRENT_SPECIFICATION_VERSION
    s.specification_version = 3

    if Gem::Version.new(Gem::RubyGemsVersion) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<activeresource>, [">= 2.3.2"])
    else
      s.add_dependency(%q<activeresource>, [">= 2.3.2"])
    end
  else
    s.add_dependency(%q<activeresource>, [">= 2.3.2"])
  end
end
