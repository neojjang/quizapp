<?php

namespace App\Constants;

class Section
{
    const TYPES = ["일반형", "답안지형"];
    const NORMAL = 1; // 일반형
    const OMR = 2; // 답안지형

    public static function isSectionType($typeId)
    {
        return in_array($typeId, [
            Section::NORMAL,
            Section::OMR
        ]);
    }
}
