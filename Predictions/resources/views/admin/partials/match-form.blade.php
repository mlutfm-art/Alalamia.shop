<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold small">{{ translate('Match_Title') }} <span class="text-muted">({{ translate('optional') }})</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title',$match->title??'') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">{{ translate('Team_1_Name') }} *</label>
        <input type="text" name="team1_name" class="form-control" required value="{{ old('team1_name',$match->team1_name??'') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">{{ translate('Team_2_Name') }} *</label>
        <input type="text" name="team2_name" class="form-control" required value="{{ old('team2_name',$match->team2_name??'') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">{{ translate('Team_1_Logo_URL') }}</label>
        <input type="url" name="team1_logo" class="form-control" placeholder="https://..." value="{{ old('team1_logo',$match->team1_logo??'') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">{{ translate('Team_2_Logo_URL') }}</label>
        <input type="url" name="team2_logo" class="form-control" placeholder="https://..." value="{{ old('team2_logo',$match->team2_logo??'') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">{{ translate('Match_Time') }} *</label>
        <input type="datetime-local" name="match_time" class="form-control" required value="{{ old('match_time',isset($match)?$match->match_time->format('Y-m-d\TH:i'):'') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">{{ translate('Prediction_Close_Time') }} *</label>
        <input type="datetime-local" name="prediction_close_time" class="form-control" required value="{{ old('prediction_close_time',isset($match)?$match->prediction_close_time->format('Y-m-d\TH:i'):'') }}">
        <div class="form-text small text-muted">{{ translate('Must_be_before_match_time') }}</div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">{{ translate('Reward_Points') }} *</label>
        <input type="number" name="reward_points" class="form-control" min="1" max="100000" required value="{{ old('reward_points',$match->reward_points??100) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold small">{{ translate('Notes') }}</label>
        <input type="text" name="notes" class="form-control" value="{{ old('notes',$match->notes??'') }}">
    </div>
</div>
