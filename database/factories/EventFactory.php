<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $availableHour=$this->faker->numberBetween(10,18);//10時～18時
        $minutes=[0,30];//00分か30分
        $mkey=array_rand($minutes);//ランダムにキーを取得
        $addHour=$this->faker->numberBetween(1,3);//イベント時間1時間～3時間

        $dummyDate=$this->faker->dateTimeThisMonth;//今月分をランダムに取得
        $startDate=$dummyDate->setTime($availableHour,$minutes[$mkey]);
        $clone=clone $startDate;//そのままmodifyするとstartDateも変わるためコピー
        $endDate=$clone->modify('+'.$addHour.'hour');

        return [
            //
            'name'=>$this->faker->name,
            'information'=>$this->faker->realText,
            'max_people'=>$this->faker->numberBetween(1,20),
            'start_date'=>$startDate,
            'end_date'=>$endDate,
            'is_visible'=>$this->faker->boolean,
        ];
    }
}
