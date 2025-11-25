<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

class BrimDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Seed system/preproduction data first (do NOT duplicate these tables)
        $needsPreprod =
        DB::table('languages')->count() === 0 ||
        DB::table('field_types')->count() === 0 ||
        DB::table('input_rules')->count() === 0 ||
        DB::table('actions')->count() === 0;

    if ($needsPreprod) {
        $this->call(SuperadminPreProductionSeeder::class);
    }

    $faker = Faker::create();

        DB::transaction(function () use ($faker) {

            // ----------------------------
            // Load "static" seeded stuff
            // ----------------------------
            $languages   = DB::table('languages')->get();
            $fieldTypes  = DB::table('field_types')->get();
            $inputRules  = DB::table('input_rules')->get();
            $actions     = DB::table('actions')->get();

            // Map field_type_id => [input_rule_id,...] via pivot input_rule_field_types
            $ruleByFieldType = [];
            $pivot = DB::table('input_rule_field_types')->get();
            foreach ($pivot as $p) {
                $ruleByFieldType[$p->field_type_id][] = $p->input_rule_id;
            }

            // ----------------------------
            // Ensure we have local dev users
            // (IDs are mirrored in prod, but OK for dev)
            // ----------------------------
            $existingUserCount = DB::table('users')->count();
            if ($existingUserCount < 20) {
                $usersToCreate = 50;
                $userRows = [];
                for ($i = 0; $i < $usersToCreate; $i++) {
                    $userRows[] = [
                        'id' => 10000 + $i + 1,
                        'name' => $faker->name(),
                        'email' => $faker->unique()->safeEmail(),
                        'default_language_id' => $languages->random()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('users')->insert($userRows);
            }
            $users = DB::table('users')->pluck('id');

            // ----------------------------
            // Categories
            // ----------------------------
            $categoryCount = 30;
            $catRows = [];
            for ($i = 0; $i < $categoryCount; $i++) {
                $catRows[] = [
                    'name' => ucfirst($faker->unique()->words(2, true)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('categories')->insert($catRows);
            $categoryIds = DB::table('categories')->pluck('id');

            // ----------------------------
            // Forms + Versions + Stages + Sections + Fields
            // ----------------------------
            $formsPerCategory = 12; // 30 * 12 = 360 forms
            $maxExtraVersions = 3;  // each form gets 1..4 versions
            $stagesPerVersion = [2, 5]; // min/max
            $sectionsPerStage = [1, 4];
            $fieldsPerSection = [3, 12];

            $formIds = [];
            foreach ($categoryIds as $categoryId) {
                for ($f = 0; $f < $formsPerCategory; $f++) {
                    $formId = DB::table('forms')->insertGetId([
                        'name' => ucfirst($faker->unique()->words(3, true)),
                        'category_id' => $faker->boolean(90) ? $categoryId : null,
                        'is_archived' => $faker->boolean(10),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $formIds[] = $formId;

                    // create 1..(1 + maxExtraVersions) versions
                    $versionsCount = 1 + $faker->numberBetween(0, $maxExtraVersions);
                    $publishedIndex = $faker->numberBetween(0, $versionsCount - 1);

                    $versionIds = [];
                    for ($v = 0; $v < $versionsCount; $v++) {
                        $status = ($v === $publishedIndex && !$faker->boolean(10)) ? 'published' : 'draft';

                        $versionId = DB::table('form_versions')->insertGetId([
                            'form_id' => $formId,
                            'version_number' => $v,
                            'status' => $status,
                            'published_at' => $status === 'published' ? now()->subDays($faker->numberBetween(0, 100)) : null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $versionIds[] = $versionId;

                        // optional translations for non-default languages
                        foreach ($languages as $lang) {
                            if (!$lang->is_default && $faker->boolean(40)) {
                                DB::table('form_version_translations')->insert([
                                    'form_version_id' => $versionId,
                                    'language_id' => $lang->id,
                                    'name' => ucfirst($faker->words(3, true))." ({$lang->code})",
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }

                        // ----- stages -----
                        $stageCount = $faker->numberBetween($stagesPerVersion[0], $stagesPerVersion[1]);

                        $stageIds = [];
                        for ($s = 0; $s < $stageCount; $s++) {
                            $stageId = DB::table('stages')->insertGetId([
                                'form_version_id' => $versionId,
                                'name' => $s === 0 ? 'Initial Stage' : ucfirst($faker->words(2, true)),
                                'is_initial' => ($s === 0),
                                'visibility_condition' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $stageIds[] = $stageId;

                            // stage access rules (mostly later stages)
                            if ($s > 0 && $faker->boolean(35)) {
                                DB::table('stage_access_rules')->insert([
                                    'stage_id' => $stageId,
                                    'allowed_users' => null,
                                    'allowed_roles' => null,
                                    'allowed_permissions' => null,
                                    'allow_authenticated_users' => $faker->boolean(70),
                                    'email_field_id' => null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }

                            // ----- sections per stage -----
                            $secCount = $faker->numberBetween($sectionsPerStage[0], $sectionsPerStage[1]);
                            for ($sec = 0; $sec < $secCount; $sec++) {
                                $sectionId = DB::table('sections')->insertGetId([
                                    'stage_id' => $stageId,
                                    'name' => ucfirst($faker->words(2, true)),
                                    'visibility_condition' => null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);

                                // ----- fields per section -----
                                $fldCount = $faker->numberBetween($fieldsPerSection[0], $fieldsPerSection[1]);
                                for ($fld = 0; $fld < $fldCount; $fld++) {
                                    $fieldType = $fieldTypes->random();
                                    $label = ucfirst($faker->words(3, true));

                                    $fieldId = DB::table('fields')->insertGetId([
                                        'section_id' => $sectionId,
                                        'field_type_id' => $fieldType->id,
                                        'label' => $label,
                                        'placeholder' => $faker->boolean(60) ? $faker->sentence(3) : null,
                                        'helper_text' => $faker->boolean(40) ? $faker->sentence(6) : null,
                                        'default_value' => null,
                                        'visibility_condition' => null,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);

                                    // maybe add translations
                                    foreach ($languages as $lang) {
                                        if (!$lang->is_default && $faker->boolean(25)) {
                                            DB::table('field_translations')->insert([
                                                'field_id' => $fieldId,
                                                'language_id' => $lang->id,
                                                'label' => $label." ({$lang->code})",
                                                'helper_text' => null,
                                                'default_value' => null,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ]);
                                        }
                                    }

                                    // attach 0..2 field_rules based on pivot compatibility
                                    $ruleIds = $ruleByFieldType[$fieldType->id] ?? [];
                                    if (!empty($ruleIds) && $faker->boolean(55)) {
                                        $howMany = $faker->numberBetween(1, min(2, count($ruleIds)));
                                        $pick = $faker->randomElements($ruleIds, $howMany);

                                        foreach ($pick as $ruleId) {
                                            DB::table('field_rules')->insert([
                                                'field_id' => $fieldId,
                                                'input_rule_id' => $ruleId,
                                                'rule_props' => null,
                                                'rule_condition' => null,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ]);
                                        }
                                    }
                                }
                            }
                        }

                        // ----- stage transitions (linear mostly) -----
                        for ($i = 0; $i < count($stageIds); $i++) {
                            $from = $stageIds[$i];
                            $to   = $stageIds[$i + 1] ?? null;
                            $toComplete = $to === null;

                            $transitionId = DB::table('stage_transitions')->insertGetId([
                                'form_version_id' => $versionId,
                                'from_stage_id' => $from,
                                'to_stage_id' => $to,
                                'to_complete' => $toComplete,
                                'label' => $toComplete ? 'Submit & Finish' : 'Next',
                                'condition' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            // attach 0..2 actions to each transition
                            if ($actions->count() > 0 && $faker->boolean(60)) {
                                $cnt = $faker->numberBetween(1, 2);
                                $actionPick = $actions->random($cnt);
                                foreach ($actionPick as $act) {
                                    DB::table('stage_transition_actions')->insert([
                                        'stage_transition_id' => $transitionId,
                                        'action_id' => $act->id,
                                        'action_props' => json_encode([]),
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // ----------------------------
            // Entries + Entry Values
            // ONLY for published versions
            // ----------------------------
            $publishedVersions = DB::table('form_versions')
                ->where('status', 'published')
                ->get();

            foreach ($publishedVersions as $ver) {
                $verStages = DB::table('stages')
                    ->where('form_version_id', $ver->id)
                    ->orderBy('id')
                    ->get();

                if ($verStages->isEmpty()) continue;

                $initialStage = $verStages->first();
                $allStageIds  = $verStages->pluck('id')->values();

                // how many entries per version
                $entryCount = $faker->numberBetween(80, 220);

                for ($e = 0; $e < $entryCount; $e++) {
                    $randomStageIndex = $faker->numberBetween(0, $allStageIds->count() - 1);
                    $currentStageId   = $allStageIds[$randomStageIndex];

                    $isComplete = ($randomStageIndex === $allStageIds->count() - 1) && $faker->boolean(70);

                    $entryId = DB::table('entries')->insertGetId([
                        'form_version_id' => $ver->id,
                        'current_stage_id' => $currentStageId,
                        'public_identifier' => (string) Str::uuid(),
                        'is_complete' => $isComplete,
                        'is_considered' => $faker->boolean(30),
                        'created_by_user_id' => $users->random(),
                        'created_at' => now()->subDays($faker->numberBetween(0, 120)),
                        'updated_at' => now()->subDays($faker->numberBetween(0, 40)),
                    ]);

                    // create values for fields in stages up to current stage
                    $stagesUpToCurrent = $allStageIds->slice(0, $randomStageIndex + 1);

                    $fieldIds = DB::table('fields')
                        ->join('sections', 'fields.section_id', '=', 'sections.id')
                        ->join('stages', 'sections.stage_id', '=', 'stages.id')
                        ->whereIn('stages.id', $stagesUpToCurrent)
                        ->select('fields.id', 'fields.field_type_id')
                        ->get();

                    foreach ($fieldIds as $finfo) {
                        DB::table('entry_values')->insert([
                            'entry_id' => $entryId,
                            'field_id' => $finfo->id,
                            'value' => $this->fakeValueForFieldType($faker, $fieldTypes, $finfo->field_type_id),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // ----------------------------
            // Notifications
            // ----------------------------
            $userIds = DB::table('users')->pluck('id');
            $notifRows = [];
            foreach ($userIds as $uid) {
                $n = $faker->numberBetween(5, 25);
                for ($i = 0; $i < $n; $i++) {
                    $notifRows[] = [
                        'user_id' => $uid,
                        'title' => ucfirst($faker->words(4, true)),
                        'body' => $faker->paragraph(),
                        'type' => $faker->randomElement(['info','success','warning','error']),
                        'icon' => null,
                        'link' => null,
                        'read_at' => $faker->boolean(50) ? now()->subDays($faker->numberBetween(0, 10)) : null,
                        'created_at' => now()->subDays($faker->numberBetween(0, 60)),
                        'updated_at' => now()->subDays($faker->numberBetween(0, 20)),
                    ];
                }
            }
            // Insert in chunks to avoid memory spikes
            foreach (array_chunk($notifRows, 1000) as $chunk) {
                DB::table('notifications')->insert($chunk);
            }
        });
    }

    private function fakeValueForFieldType($faker, $fieldTypes, int $fieldTypeId): string
    {
        $name = $fieldTypes->firstWhere('id', $fieldTypeId)->name ?? '';

        return match ($name) {
            'Text Input', 'Text Area' => $faker->sentence(),
            'Email Input' => $faker->unique()->safeEmail(),
            'Number Input', 'Currency Input', 'Percentage Input', 'Slider', 'Rating' => (string) $faker->numberBetween(0, 100),
            'Phone Input' => $faker->phoneNumber(),
            'Date Input' => $faker->date('Y-m-d'),
            'Time Input' => $faker->time('H:i'),
            'DateTime Input' => $faker->dateTime()->format('Y-m-d H:i:s'),
            'Checkbox', 'Toggle Switch' => $faker->boolean() ? '1' : '0',
            'Radio Button', 'Dropdown Select', 'Multi-Select' => $faker->word(),
            'URL Input' => $faker->url(),
            'Address Input' => json_encode([
                'street' => $faker->streetAddress(),
                'city' => $faker->city(),
                'state' => $faker->state(),
                'postal_code' => $faker->postcode(),
                'country' => $faker->country(),
            ]),
            'Location Picker' => json_encode([
                'lat' => $faker->latitude(),
                'lng' => $faker->longitude(),
                'address' => $faker->address(),
            ]),
            default => (string) $faker->sentence(),
        };
    }
}
