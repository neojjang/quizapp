<?php

namespace App\Constants;

class Question {
    const TYPES = ["선택형", "번역:서술형(첫번째 답만 사용)", "영작:서술형(첫번째 답만 사용)", "단답형"];
    const SELECTIVE = 0; // 선택형
    const TRANSLATION = 1; // 번역:서술형
    const ENGLISH_COMPOSITION = 2; // 영작:서술형
    const SHORT_ANSWER = 3; // 단답형
}
