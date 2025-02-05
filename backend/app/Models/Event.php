<?php

declare(strict_types=1);

namespace HiEvents\Models;

use HiEvents\DomainObjects\Generated\EventDomainObjectAbstract;
use HiEvents\Models\Traits\HasImages;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Event extends BaseModel
{
    use HasImages;

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class)->orderBy('order');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function event_settings(): HasOne
    {
        return $this->hasOne(EventSetting::class);
    }

    public function promo_codes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    public function check_in_lists(): HasMany
    {
        return $this->hasMany(CheckInList::class);
    }

    public function capacity_assignments(): HasMany
    {
        return $this->hasMany(CapacityAssignment::class);
    }

    protected static function generateUniqueShortId(): string
    {
        do {
            $shortId = Str::random(8);
        } while (static::where('short_id', $shortId)->exists());

        return $shortId;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (Event $event) {
            $event->user_id = auth()->user()->id;
            $event->short_id = static::generateUniqueShortId();
            $event->timezone = $event->timezone ?? config('app.timezone', 'UTC');
        });
    }

    protected function getFillableFields(): array
    {
        return [
            EventDomainObjectAbstract::TITLE,
            EventDomainObjectAbstract::DESCRIPTION,
            EventDomainObjectAbstract::START_DATE,
            EventDomainObjectAbstract::END_DATE,
            EventDomainObjectAbstract::STATUS,
            EventDomainObjectAbstract::ORGANIZER_ID,
            EventDomainObjectAbstract::ACCOUNT_ID,
            EventDomainObjectAbstract::SHORT_ID,
            EventDomainObjectAbstract::TIMEZONE,
            'tipoticket',
            'map',
        ];
    }
 
    protected function getCastMap(): array 
    {
        return [
            EventDomainObjectAbstract::START_DATE => 'datetime',
            EventDomainObjectAbstract::END_DATE => 'datetime',
            EventDomainObjectAbstract::ATTRIBUTES => 'array',
            EventDomainObjectAbstract::LOCATION_DETAILS => 'array',
            'tipoticket' => 'string',
            'map' => 'string',
        ];
    }
}
 