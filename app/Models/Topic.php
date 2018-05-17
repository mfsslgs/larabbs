<?php

namespace App\Models;

use App\Models\User;
use App\Models\Category;

class Topic extends Model
{
    protected $fillable = ['title', 'body', 'category_id', 'excerpt', 'slug'];

    public function user(){

      return $this->belongsTo(User::class);

    }

    public function category(){

      return $this->belongsTo(Category::class);
    }

    public function scopeWithOrder($query,$order){

        switch($order){
          case 'recent':
            $query->recent();
            break;

          default:
            $query->recentReplied();
            break;
        }

        return $query->with('user','category');

    }

    public function scopeRecentReplied($query){
      return $query->orderBy('updated_at','desc');
    }

    public function scopeRecent($query){
      return $query->orderBy('created_at','desc');
    }

    public function link($params = []){
      return route('topics.show',array_merge([$this->id,$this->slug],$params));
    }
}
