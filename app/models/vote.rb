class Vote < ActiveRecord::Base
  self.table_name = "pw_vote"
  belongs_to :division
  belongs_to :member, foreign_key: "mp_id"

  delegate :party, :party_long2, :name, :name_without_title, :electorate, to: :member
  delegate :whip_guess, :free?, to: :whip
  delegate :date, to: :division

  def whip
    division.whips.where(party: party).first
  end

  def rebellion?
    !free? && vote_without_tell != whip_guess
  end

  def teller?
    vote[0..3] == 'tell'
  end

  def vote_without_tell
    vote.gsub('tell', '')
  end

  def role
    if teller? && free?
      "free teller"
    elsif teller? && rebellion?
      "rebel teller"
    elsif teller?
      "teller"
    elsif rebellion?
      "rebel"
    elsif free?
      "free"
    else
      "loyal"
    end
  end
end
