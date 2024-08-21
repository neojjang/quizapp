<?php

namespace App\Constants;

/**
 * 시험 유형
 */
class Section
{
    const TYPES = ["일반형", "OMR답안지형", "영작나열형", "듣기평가"];
    const NORMAL = 1; // 일반형
    const OMR = 2; // OMR답안지형
    const ENGLISH_COMPOSITION_CLICK = 3; // 영작나열형
    const LISTENING_TEST = 4; // 듣기평가

    public static function isSectionType($typeId)
    {
        return in_array($typeId, [
            Section::NORMAL,
            Section::OMR,
            Section::ENGLISH_COMPOSITION_CLICK,
            Section::LISTENING_TEST,
        ]);
    }
}
