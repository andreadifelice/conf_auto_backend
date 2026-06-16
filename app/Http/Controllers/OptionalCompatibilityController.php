<?php

namespace App\Http\Controllers;

use App\Models\Optional;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;

class OptionalCompatibilityController extends Controller
{
    public function index()
    {
        $compatibilities = Optional::query()
            ->with(['requires', 'excludes'])
            ->orderBy('name', 'asc')
            ->get()
            ->flatMap(function (Optional $optional) {
                $requires = $optional->requires->map(function (Optional $relatedOptional) use ($optional) {
                    return [
                        'optional_id' => $optional->id,
                        'optional_name' => $optional->name,
                        'type' => 'requires',
                        'related_optional_id' => $relatedOptional->id,
                        'related_optional_name' => $relatedOptional->name,
                    ];
                });

                $excludes = $optional->excludes->map(function (Optional $relatedOptional) use ($optional) {
                    return [
                        'optional_id' => $optional->id,
                        'optional_name' => $optional->name,
                        'type' => 'excludes',
                        'related_optional_id' => $relatedOptional->id,
                        'related_optional_name' => $relatedOptional->name,
                    ];
                });

                return $requires->concat($excludes);
            })
            ->values();

        return response()->json($compatibilities);
    }

    public function show(Optional $optional, string $type, Optional $relatedOptional)
    {
        $relation = $this->relationForType($optional, $type);
        $exists = $relation->where('optionals.id', $relatedOptional->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Compatibilita optional non trovata.',
            ], 404);
        }

        return response()->json([
            'optional_id' => $optional->id,
            'optional_name' => $optional->name,
            'type' => $type,
            'related_optional_id' => $relatedOptional->id,
            'related_optional_name' => $relatedOptional->name,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'optional_id' => ['required', 'integer', 'exists:optionals,id'],
            'type' => ['required', 'in:requires,excludes'],
            'related_optional_id' => ['required', 'integer', 'exists:optionals,id'],
        ]);

        if ((int) $data['optional_id'] === (int) $data['related_optional_id']) {
            return response()->json([
                'message' => 'Un optional non puo essere compatibile con se stesso.',
            ], 422);
        }

        $optional = Optional::query()->findOrFail($data['optional_id']);
        $relation = $this->relationForType($optional, $data['type']);
        $relatedOptionalId = (int) $data['related_optional_id'];

        $alreadyLinked = $relation->where('optionals.id', $relatedOptionalId)->exists();

        if ($alreadyLinked) {
            return response()->json([
                'message' => 'Compatibilita gia presente.',
            ], 422);
        }

        $relation->attach($relatedOptionalId);

        return response()->json([
            'message' => 'Compatibilita optional creata con successo!',
            'data' => [
                'optional_id' => $optional->id,
                'type' => $data['type'],
                'related_optional_id' => $relatedOptionalId,
            ],
        ], 201);
    }

    public function update(Request $request, Optional $optional, string $type, Optional $relatedOptional)
    {
        $data = $request->validate([
            'related_optional_id' => ['required', 'integer', 'exists:optionals,id'],
        ]);

        $relation = $this->relationForType($optional, $type);
        $exists = $relation->where('optionals.id', $relatedOptional->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Compatibilita optional non trovata.',
            ], 404);
        }

        $newRelatedOptionalId = (int) $data['related_optional_id'];

        if ($newRelatedOptionalId === $optional->id) {
            return response()->json([
                'message' => 'Un optional non puo essere compatibile con se stesso.',
            ], 422);
        }

        if ($newRelatedOptionalId !== $relatedOptional->id) {
            $alreadyLinked = $relation->where('optionals.id', $newRelatedOptionalId)->exists();

            if ($alreadyLinked) {
                return response()->json([
                    'message' => 'Compatibilita gia presente.',
                ], 422);
            }

            $relation->detach($relatedOptional->id);
            $relation->attach($newRelatedOptionalId);
        }

        return response()->json([
            'message' => 'Compatibilita optional aggiornata con successo!',
            'data' => [
                'optional_id' => $optional->id,
                'type' => $type,
                'related_optional_id' => $newRelatedOptionalId,
            ],
        ], 200);
    }

    public function destroy(Optional $optional, string $type, Optional $relatedOptional)
    {
        $relation = $this->relationForType($optional, $type);
        $exists = $relation->where('optionals.id', $relatedOptional->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Compatibilita optional non trovata.',
            ], 404);
        }

        $relation->detach($relatedOptional->id);

        return response()->json([
            'message' => 'Compatibilita optional eliminata con successo!',
        ], 200);
    }

    private function relationForType(Optional $optional, string $type): BelongsToMany
    {
        return $type === 'requires' ? $optional->requires() : $optional->excludes();
    }
}
