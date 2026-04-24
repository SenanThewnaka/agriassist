<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Farm extends Model {
    protected $fillable = ['farmer_id', 'latitude', 'longitude', 'farm_name', 'soil_type', 'farm_size', 'irrigation_source', 'elevation', 'district'];
    public function farmer(): BelongsTo { return $this->belongsTo(User::class, 'farmer_id'); }
    public function cropSeasons(): HasMany { return $this->hasMany(CropSeason::class); }
    public function activities(): HasMany { return $this->hasMany(FarmActivity::class); }
    public function weatherRecords(): HasMany { return $this->hasMany(WeatherRecord::class); }
    public function weatherAlerts(): HasMany { return $this->hasMany(WeatherAlert::class); }
    public function pestAlerts(): HasMany { return $this->hasMany(PestAlert::class); }
    public function aiInsights(): HasMany { return $this->hasMany(AiInsight::class); }
    public function diseaseDetections(): HasMany { return $this->hasMany(DiseaseDetection::class); }
    public function soilReports(): HasMany { return $this->hasMany(SoilReport::class); }
}