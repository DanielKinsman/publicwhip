require 'spec_helper'
require 'net/http'
# Compare results of rendering pages via rails and via the old php app

def tidy(text)
  File.open("temp.html", "w") {|f| f.write(text) }
  # Requires HTML Tidy (http://tidy.sourceforge.net/) version 14 June 2007 or later
  # Can install on OS X with "brew install tidy"
  # Note the version installed with OS X by default is a version that's too old
  system("/usr/local/bin/tidy --sort-attributes alpha -utf8 -q -m temp.html")
  r = File.read("temp.html")
  # Make sure that comments of the form <!-- comment --> are followed by a new line
  File.delete("temp.html")
  r.gsub("--><", "-->\n<")
end

# Convert into a form where html can be reliably diff'd
def normalise_html(text)
  tidy(text)
end

def compare_html(old_html, new_html)
  n = normalise_html(new_html)
  o = normalise_html(old_html)

  if n != o
    # Write it out to a file
    File.open("old.html", "w") {|f| f.write(o.to_s)}
    File.open("new.html", "w") {|f| f.write(n.to_s)}
    raise "Don't match. Writing to file old.html and new.html"
  end
end

describe "Comparing" do
  def compare(path)
    get path
    text = Net::HTTP.get('localhost', path)
    text.force_encoding(Encoding::UTF_8)
    compare_html(text, response.body)
  end

  before :each do
    m = Member.create!(first_name: "Tony", last_name: "Abbott", party: "Liberal Party",
      constituency: "Warringah", house: "commons",
      gid: "", source_gid: "", title: "")
    # TODO don't know what aye_majority does yet
    MemberInfo.create!(mp_id: m.id, rebellions: 0, tells: 0, votes_possible: 1, votes_attended: 0, aye_majority: 0)

    m = Member.create!(first_name: "Kevin", last_name: "Rudd", party: "Australian Labor Party",
      constituency: "Griffith", house: "commons",
      gid: "", source_gid: "", title: "")
    # TODO don't know what aye_majority does yet
    MemberInfo.create!(mp_id: m.id, rebellions: 0, tells: 0, votes_possible: 1, votes_attended: 1, aye_majority: -1)

    m = Member.create!(first_name: "Christine", last_name: "Milne", party: "Australian Greens",
      constituency: "Tasmania", house: "lords",
      gid: "", source_gid: "", title: "")
    # TODO don't know what aye_majority does yet
    MemberInfo.create!(mp_id: m.id, rebellions: 0, tells: 0, votes_possible: 1, votes_attended: 1, aye_majority: -1)
  end
  
  it "/" do
    compare("/")
  end

  describe "representatives" do
    it "/mps.php" do
      compare("/mps.php")
    end

    it "/mps.php?sort=lastname" do
      compare("/mps.php?sort=lastname")
    end

    it "/mps.php?sort=constituency" do
      compare("/mps.php?sort=constituency")
    end

    it "/mps.php?sort=party" do
      compare("/mps.php?sort=party")
    end

    it "/mps.php?sort=rebellions" do
      compare("/mps.php?sort=rebellions")
    end

    it "/mps.php?sort=attendance" do
      compare("/mps.php?sort=attendance")
    end
  end

  describe "senators" do
    it "/mps.php?house=senate" do
      compare("/mps.php?house=senate")
    end

    it "/mps.php?house=senate&sort=lastname" do
      compare("/mps.php?house=senate&sort=lastname")
    end

    it "/mps.php?house=senate&sort=constituency" do
      compare("/mps.php?house=senate&sort=constituency")
    end

    it "/mps.php?house=senate&sort=party" do
      compare("/mps.php?house=senate&sort=party")
    end

    it "/mps.php?house=senate&sort=rebellions" do
      compare("/mps.php?house=senate&sort=rebellions")
    end

    it "/mps.php?house=senate&sort=attendance" do
      compare("/mps.php?house=senate&sort=attendance")
    end
  end

  describe "divisions" do
    before :each do
      d = Division.create!(division_date: Date.new(2013,3,14), clock_time: "010:56:00", division_number: 1, house: "commons",
        division_name: "Bills &#8212; National Disability Insurance Scheme Bill 2012; Consideration in Detail",
        source_url: "http://parlinfo.aph.gov.au/parlInfo/search/display/display.w3p;adv=yes;orderBy=_fragment_number,doc_date-rev;page=0;query=Dataset%3Ahansardr,hansardr80%20Date%3A14%2F3%2F2013;rec=0;resCount=Default",
        debate_url: "",
        motion: '<p class="speaker">Jenny Macklin</p><p>I present a supplementary explanatory memorandum to the bill and ask leave of the House to move government amendments (1) to (77), as circulated, together.</p>',
        notes: "",
        source_gid: "",
        debate_gid: "uk.org.publicwhip/debate/2013-03-14.17.1")
      DivisionInfo.create!(division_id: d.id, rebellions: 0, tells: 0, turnout: 136,
        possible_turnout: 150, aye_majority: -1)
      Whip.create!(division_id: d.id, party: "Australian Labor Party", aye_votes: 0, aye_tells: 0, no_votes: 1, no_tells: 0, both_votes: 0, abstention_votes: 0, possible_votes: 1, whip_guess: "no")
      Whip.create!(division_id: d.id, party: "Liberal Party", aye_votes: 0, aye_tells: 0, no_votes: 0, no_tells: 0, both_votes: 0, abstention_votes: 0, possible_votes: 1, whip_guess: "unknown")

      d = Division.create!(division_date: Date.new(2013,3,14), division_number: 1, house: "lords",
        division_name: "Motions &#8212; Renewable Energy Certificates",
        source_url: "http://aph.gov.au/somedebate", debate_url: "",
        motion: "",
        notes: "",
        source_gid: "",
        debate_gid: "uk.org.publicwhip/lords/2013-03-14.22.1")
      DivisionInfo.create!(division_id: d.id, rebellions: 0, tells: 0, turnout: 69,
        possible_turnout: 88, aye_majority: -3)
      Whip.create!(division_id: d.id, party: "Australian Greens", aye_votes: 0, aye_tells: 0, no_votes: 1, no_tells: 0, both_votes: 0, abstention_votes: 0, possible_votes: 1, whip_guess: "no")
    end

    it "division" do
      compare("/division.php?date=2013-03-14&number=1&house=representatives")
      compare("/division.php?date=2013-03-14&number=1")
      compare("/division.php?date=2013-03-14&number=1&house=senate")
      compare("/division.php?date=2013-03-14&number=1&display=allvotes")
      compare("/division.php?date=2013-03-14&number=1&display=allvotes&sort=name")
      compare("/division.php?date=2013-03-14&number=1&display=allvotes&sort=vote")
      compare("/division.php?date=2013-03-14&number=1&display=allpossible")
      compare("/division.php?date=2013-03-14&number=1&display=allpossible&sort=name")
      compare("/division.php?date=2013-03-14&number=1&display=allpossible&sort=vote")
    end

    it "divisions" do
      compare("/divisions.php")
      compare("/divisions.php?rdisplay=2007")
      compare("/divisions.php?rdisplay=2004")
      compare("/divisions.php?rdisplay=all")
      compare("/divisions.php?rdisplay2=rebels")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels")
      compare("/divisions.php?house=representatives")
      compare("/divisions.php?rdisplay=2007&house=representatives")
      compare("/divisions.php?rdisplay=2004&house=representatives")
      compare("/divisions.php?rdisplay=all&house=representatives")
      compare("/divisions.php?rdisplay2=rebels&house=representatives")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&house=representatives")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&house=representatives")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&house=representatives")
      compare("/divisions.php?house=senate")
      compare("/divisions.php?rdisplay=2007&house=senate")
      compare("/divisions.php?rdisplay=2004&house=senate")
      compare("/divisions.php?rdisplay=all&house=senate")
      compare("/divisions.php?rdisplay2=rebels&house=senate")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&house=senate")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&house=senate")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&house=senate")

      compare("/divisions.php?sort=subject")
      compare("/divisions.php?rdisplay=2007&sort=subject")
      compare("/divisions.php?rdisplay=2004&sort=subject")
      compare("/divisions.php?rdisplay=all&sort=subject")
      compare("/divisions.php?rdisplay2=rebels&sort=subject")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&sort=subject")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&sort=subject")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&sort=subject")
      compare("/divisions.php?house=representatives&sort=subject")
      compare("/divisions.php?rdisplay=2007&house=representatives&sort=subject")
      compare("/divisions.php?rdisplay=2004&house=representatives&sort=subject")
      compare("/divisions.php?rdisplay=all&house=representatives&sort=subject")
      compare("/divisions.php?rdisplay2=rebels&house=representatives&sort=subject")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&house=representatives&sort=subject")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&house=representatives&sort=subject")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&house=representatives&sort=subject")
      compare("/divisions.php?house=senate&sort=subject")
      compare("/divisions.php?rdisplay=2007&house=senate&sort=subject")
      compare("/divisions.php?rdisplay=2004&house=senate&sort=subject")
      compare("/divisions.php?rdisplay=all&house=senate&sort=subject")
      compare("/divisions.php?rdisplay2=rebels&house=senate&sort=subject")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&house=senate&sort=subject")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&house=senate&sort=subject")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&house=senate&sort=subject")

      compare("/divisions.php?sort=rebellions")
      compare("/divisions.php?rdisplay=2007&sort=rebellions")
      compare("/divisions.php?rdisplay=2004&sort=rebellions")
      compare("/divisions.php?rdisplay=all&sort=rebellions")
      compare("/divisions.php?rdisplay2=rebels&sort=rebellions")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&sort=rebellions")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&sort=rebellions")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&sort=rebellions")
      compare("/divisions.php?house=representatives&sort=rebellions")
      compare("/divisions.php?rdisplay=2007&house=representatives&sort=rebellions")
      compare("/divisions.php?rdisplay=2004&house=representatives&sort=rebellions")
      compare("/divisions.php?rdisplay=all&house=representatives&sort=rebellions")
      compare("/divisions.php?rdisplay2=rebels&house=representatives&sort=rebellions")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&house=representatives&sort=rebellions")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&house=representatives&sort=rebellions")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&house=representatives&sort=rebellions")
      compare("/divisions.php?house=senate&sort=rebellions")
      compare("/divisions.php?rdisplay=2007&house=senate&sort=rebellions")
      compare("/divisions.php?rdisplay=2004&house=senate&sort=rebellions")
      compare("/divisions.php?rdisplay=all&house=senate&sort=rebellions")
      compare("/divisions.php?rdisplay2=rebels&house=senate&sort=rebellions")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&house=senate&sort=rebellions")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&house=senate&sort=rebellions")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&house=senate&sort=rebellions")

      compare("/divisions.php?sort=turnout")
      compare("/divisions.php?rdisplay=2007&sort=turnout")
      compare("/divisions.php?rdisplay=2004&sort=turnout")
      compare("/divisions.php?rdisplay=all&sort=turnout")
      compare("/divisions.php?rdisplay2=rebels&sort=turnout")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&sort=turnout")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&sort=turnout")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&sort=turnout")
      compare("/divisions.php?house=representatives&sort=turnout")
      compare("/divisions.php?rdisplay=2007&house=representatives&sort=turnout")
      compare("/divisions.php?rdisplay=2004&house=representatives&sort=turnout")
      compare("/divisions.php?rdisplay=all&house=representatives&sort=turnout")
      compare("/divisions.php?rdisplay2=rebels&house=representatives&sort=turnout")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&house=representatives&sort=turnout")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&house=representatives&sort=turnout")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&house=representatives&sort=turnout")
      compare("/divisions.php?house=senate&sort=turnout")
      compare("/divisions.php?rdisplay=2007&house=senate&sort=turnout")
      compare("/divisions.php?rdisplay=2004&house=senate&sort=turnout")
      compare("/divisions.php?rdisplay=all&house=senate&sort=turnout")
      compare("/divisions.php?rdisplay2=rebels&house=senate&sort=turnout")
      compare("/divisions.php?rdisplay=2007&rdisplay2=rebels&house=senate&sort=turnout")
      compare("/divisions.php?rdisplay=2004&rdisplay2=rebels&house=senate&sort=turnout")
      compare("/divisions.php?rdisplay=all&rdisplay2=rebels&house=senate&sort=turnout")
    end
  end
end
