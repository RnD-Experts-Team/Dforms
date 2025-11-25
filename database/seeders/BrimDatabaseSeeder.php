<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class BrimDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Run preprod/system seeder only if needed
        $needsPreprod =
            DB::table('languages')->count() === 0 ||
            DB::table('field_types')->count() === 0 ||
            DB::table('input_rules')->count() === 0 ||
            DB::table('actions')->count() === 0;

        if ($needsPreprod) {
            $this->call(SuperadminPreProductionSeeder::class);
        }

        DB::disableQueryLog();
        $faker = Faker::create();

        // Optional: huge speedup in dev
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::transaction(function () use ($faker) {

            // ----------------------------
            // Load system data
            // ----------------------------
            $languages  = DB::table('languages')->get();
            $fieldTypes = DB::table('field_types')->get();
            $actions    = DB::table('actions')->get();

            $ruleByFieldType = [];
            $pivot = DB::table('input_rule_field_types')->get();
            foreach ($pivot as $p) {
                $ruleByFieldType[$p->field_type_id][] = $p->input_rule_id;
            }

            // ----------------------------
            // Ensure local dev users
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
            // Categories (bulk)
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
            // (still some insertGetId needed for graph, but tighter)
            // ----------------------------
            $formsPerCategory = 12; // 360 forms
            $maxExtraVersions = 3;  // 1..4 versions
            $stagesPerVersion = [2, 5];
            $sectionsPerStage = [1, 4];
            $fieldsPerSection = [3, 12];

            foreach ($categoryIds as $categoryId) {
                for ($f = 0; $f < $formsPerCategory; $f++) {

                    $formId = DB::table('forms')->insertGetId([
                        'name' => ucfirst($faker->unique()->words(3, true)),
                        'category_id' => $faker->boolean(90) ? $categoryId : null,
                        'is_archived' => $faker->boolean(10),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $versionsCount = 1 + $faker->numberBetween(0, $maxExtraVersions);
                    $publishedIndex = $faker->numberBetween(0, $versionsCount - 1);

                    for ($v = 0; $v < $versionsCount; $v++) {
                        $status = ($v === $publishedIndex && !$faker->boolean(10)) ? 'published' : 'draft';

                        $versionId = DB::table('form_versions')->insertGetId([
                            'form_id' => $formId,
                            'version_number' => $v,
                            'status' => $status,
                            'published_at' => $status === 'published'
                                ? now()->subDays($faker->numberBetween(0, 100))
                                : null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // translations (bulk per version)
                        $fvTrans = [];
                        foreach ($languages as $lang) {
                            if (!$lang->is_default && $faker->boolean(40)) {
                                $fvTrans[] = [
                                    'form_version_id' => $versionId,
                                    'language_id' => $lang->id,
                                    'name' => ucfirst($faker->words(3, true))." ({$lang->code})",
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                        if ($fvTrans) {
                            DB::table('form_version_translations')->insert($fvTrans);
                        }

                        // ----- stages -----
                        $stageCount = $faker->numberBetween($stagesPerVersion[0], $stagesPerVersion[1]);
                        $stageIds = [];

                        for ($s = 0; $s < $stageCount; $s++) {
                            $stageIds[] = DB::table('stages')->insertGetId([
                                'form_version_id' => $versionId,
                                'name' => $s === 0 ? 'Initial Stage' : ucfirst($faker->words(2, true)),
                                'is_initial' => ($s === 0),
                                'visibility_condition' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            if ($s > 0 && $faker->boolean(35)) {
                                DB::table('stage_access_rules')->insert([
                                    'stage_id' => $stageIds[$s],
                                    'allowed_users' => null,
                                    'allowed_roles' => null,
                                    'allowed_permissions' => null,
                                    'allow_authenticated_users' => $faker->boolean(70),
                                    'email_field_id' => null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }

                            // ----- sections + fields -----
                            $secCount = $faker->numberBetween($sectionsPerStage[0], $sectionsPerStage[1]);

                            for ($sec = 0; $sec < $secCount; $sec++) {
                                $sectionId = DB::table('sections')->insertGetId([
                                    'stage_id' => $stageIds[$s],
                                    'name' => ucfirst($faker->words(2, true)),
                                    'visibility_condition' => null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);

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

                                    // field translations (bulk per field)
                                    $fTrans = [];
                                    foreach ($languages as $lang) {
                                        if (!$lang->is_default && $faker->boolean(25)) {
                                            $fTrans[] = [
                                                'field_id' => $fieldId,
                                                'language_id' => $lang->id,
                                                'label' => $label." ({$lang->code})",
                                                'helper_text' => null,
                                                'default_value' => null,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ];
                                        }
                                    }
                                    if ($fTrans) {
                                        DB::table('field_translations')->insert($fTrans);
                                    }

                                    // field_rules (bulk per field)
                                    $ruleIds = $ruleByFieldType[$fieldType->id] ?? [];
                                    if (!empty($ruleIds) && $faker->boolean(55)) {
                                        $howMany = $faker->numberBetween(1, min(2, count($ruleIds)));
                                        $pick = $faker->randomElements($ruleIds, $howMany);

                                        $frRows = [];
                                        foreach ($pick as $ruleId) {
                                            $frRows[] = [
                                                'field_id' => $fieldId,
                                                'input_rule_id' => $ruleId,
                                                'rule_props' => null,
                                                'rule_condition' => null,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ];
                                        }
                                        DB::table('field_rules')->insert($frRows);
                                    }
                                }
                            }
                        }

                        // ----- stage transitions (bulk) -----
                        $transitionIds = [];
                        for ($i = 0; $i < count($stageIds); $i++) {
                            $from = $stageIds[$i];
                            $to   = $stageIds[$i + 1] ?? null;
                            $toComplete = $to === null;

                            $transitionIds[] = DB::table('stage_transitions')->insertGetId([
                                'form_version_id' => $versionId,
                                'from_stage_id' => $from,
                                'to_stage_id' => $to,
                                'to_complete' => $toComplete,
                                'label' => $toComplete ? 'Submit & Finish' : 'Next',
                                'condition' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // transition actions (bulk)
                        if ($actions->count() > 0) {
                            $taRows = [];
                            foreach ($transitionIds as $tid) {
                                if ($faker->boolean(60)) {
                                    $cnt = $faker->numberBetween(1, 2);
                                    $actionPick = $actions->random($cnt);
                                    foreach ($actionPick as $act) {
                                        $taRows[] = [
                                            'stage_transition_id' => $tid,
                                            'action_id' => $act->id,
                                            'action_props' => json_encode([]),
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ];
                                    }
                                }
                            }
                            if ($taRows) {
                                DB::table('stage_transition_actions')->insert($taRows);
                            }
                        }
                    }
                }
            }

            // ----------------------------
            // Entries + Entry Values (SUPER FAST)
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

                $allStageIds = $verStages->pluck('id')->values();

                // Preload all fields for this version grouped by stage
                $fieldsByStage = [];
                $fields = DB::table('fields')
                    ->join('sections', 'fields.section_id', '=', 'sections.id')
                    ->whereIn('sections.stage_id', $allStageIds)
                    ->select('fields.id', 'fields.field_type_id', 'sections.stage_id')
                    ->get();

                foreach ($fields as $f) {
                    $fieldsByStage[$f->stage_id][] = [
                        'id' => $f->id,
                        'field_type_id' => $f->field_type_id,
                    ];
                }

                // Build cumulative fields up to each stage index
                $cumulativeFieldsByIndex = [];
                $running = [];
                foreach ($allStageIds as $idx => $sid) {
                    foreach (($fieldsByStage[$sid] ?? []) as $fi) {
                        $running[] = $fi;
                    }
                    $cumulativeFieldsByIndex[$idx] = $running;
                }

                // Bulk insert entries
                $entryCount = $faker->numberBetween(80, 220);
                $entriesRows = [];

                for ($e = 0; $e < $entryCount; $e++) {
                    $randomStageIndex = $faker->numberBetween(0, $allStageIds->count() - 1);
                    $currentStageId   = $allStageIds[$randomStageIndex];

                    $entriesRows[] = [
                        'form_version_id' => $ver->id,
                        'current_stage_id' => $currentStageId,
                        'public_identifier' => (string) Str::uuid(),
                        'is_complete' => ($randomStageIndex === $allStageIds->count() - 1) && $faker->boolean(70),
                        'is_considered' => $faker->boolean(30),
                        'created_by_user_id' => $users->random(),
                        'created_at' => now()->subDays($faker->numberBetween(0, 120)),
                        'updated_at' => now()->subDays($faker->numberBetween(0, 40)),
                        '_stage_index' => $randomStageIndex, // helper only
                    ];
                }

                $insertedEntryMetas = [];

                foreach (array_chunk($entriesRows, 500) as $chunk) {
                    $toInsert = array_map(function ($r) {
                        unset($r['_stage_index']);
                        return $r;
                    }, $chunk);

                    DB::table('entries')->insert($toInsert);

                    // Pull back last inserted IDs for this version
                    $lastIds = DB::table('entries')
                        ->where('form_version_id', $ver->id)
                        ->orderByDesc('id')
                        ->limit(count($chunk))
                        ->pluck('id')
                        ->reverse()
                        ->values();

                    foreach ($chunk as $i => $row) {
                        $insertedEntryMetas[] = [
                            'id' => $lastIds[$i],
                            'stage_index' => $row['_stage_index'],
                        ];
                    }
                }

                // Bulk insert entry_values
                $valuesBuffer = [];

                foreach ($insertedEntryMetas as $em) {
                    $entryId = $em['id'];
                    $stageIndex = $em['stage_index'];

                    $fieldsUpToStage = $cumulativeFieldsByIndex[$stageIndex] ?? [];

                    foreach ($fieldsUpToStage as $fi) {
                        $valuesBuffer[] = [
                            'entry_id' => $entryId,
                            'field_id' => $fi['id'],
                            'value' => $this->fakeValueForFieldType($faker, $fieldTypes, $fi['field_type_id']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (count($valuesBuffer) >= 5000) {
                        DB::table('entry_values')->insert($valuesBuffer);
                        $valuesBuffer = [];
                    }
                }

                if ($valuesBuffer) {
                    DB::table('entry_values')->insert($valuesBuffer);
                }
            }

            // ----------------------------
            // Notifications (bulk)
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

            foreach (array_chunk($notifRows, 1000) as $chunk) {
                DB::table('notifications')->insert($chunk);
            }
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function fakeValueForFieldType($faker, $fieldTypes, int $fieldTypeId): string
    {
        $name = $fieldTypes->firstWhere('id', $fieldTypeId)->name ?? '';

        return match ($name) {
            'Text Input', 'Text Area' => $faker->sentence(),
            'Email Input' => $faker->unique()->safeEmail(),
            'Number Input', 'Currency Input', 'Percentage Input', 'Slider', 'Rating'
                => (string) $faker->numberBetween(0, 100),
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
