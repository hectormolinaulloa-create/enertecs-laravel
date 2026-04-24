<?php

if (!function_exists('socColor')) {
    function socColor(?int $soc): string
    {
        if ($soc === null) return '#5A80AB';
        if ($soc >= 60)   return '#34D399';
        if ($soc >= 30)   return '#F59E0B';
        return '#F87171';
    }
}

if (!function_exists('socGauge')) {
    /**
     * SVG arc gauge para batería SOC (270°, apertura abajo).
     * Replica SocGauge de VrmSection.tsx.
     */
    function socGauge(?int $soc, int $size = 84): string
    {
        $r   = $size * 0.365;
        $sw  = $size * 0.092;
        $cx  = $cy = $size / 2;
        $C   = 2 * M_PI * $r;
        $arc = $C * 0.735;
        $gap = $C - $arc;
        $col = socColor($soc);
        $fill = $soc !== null ? max(($soc / 100) * $arc, 0) : 0;
        $blank = $C - $fill;

        $trackDash  = "{$arc} {$gap}";
        $arcDash    = "{$fill} {$blank}";
        $fontSize   = round($size * 0.19, 1);
        $labelSize  = round($size * 0.13, 1);
        $labelY     = round($cy + $size * 0.215, 1);
        $socText    = $soc !== null ? "{$soc}%" : '—';

        $arcPath = $soc !== null && $soc > 0
            ? "<circle cx=\"{$cx}\" cy=\"{$cy}\" r=\"{$r}\" fill=\"none\"
                 stroke=\"{$col}\" stroke-width=\"{$sw}\" stroke-linecap=\"round\"
                 stroke-dasharray=\"{$arcDash}\"
                 transform=\"rotate(132 {$cx} {$cy})\"
                 style=\"transition:stroke-dasharray .9s cubic-bezier(.4,0,.2,1)\"/>"
            : '';

        return <<<SVG
<svg width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
  <circle cx="{$cx}" cy="{$cy}" r="{$r}" fill="none"
    stroke="#1A2D4A" stroke-width="{$sw}" stroke-linecap="round"
    stroke-dasharray="{$trackDash}"
    transform="rotate(132 {$cx} {$cy})"/>
  {$arcPath}
  <text x="{$cx}" y="{$cy}" text-anchor="middle" dominant-baseline="central"
    font-size="{$fontSize}" font-weight="800" fill="{$col}">{$socText}</text>
  <text x="{$cx}" y="{$labelY}" text-anchor="middle"
    font-size="{$labelSize}" fill="#5A80AB" font-weight="600"
    letter-spacing="0.05em">SOC</text>
</svg>
SVG;
    }
}

if (!function_exists('miniSpark')) {
    /**
     * Minigrafico sparkline SVG inline.
     * Replica MiniSpark de VrmSection.tsx.
     */
    function miniSpark(array $values, string $color = '#3B82F6'): string
    {
        if (empty($values)) return '';

        $W = 80; $H = 29;
        $max   = max($values);
        $min   = min($values);
        $range = $max - $min ?: 1;
        $pad   = $H * 0.15;
        $count = count($values);
        $step  = $count > 1 ? $W / ($count - 1) : 0;

        $pts = [];
        foreach ($values as $i => $v) {
            $pts[] = [
                round($i * $step, 1),
                round($pad + ($H - 2 * $pad) - (($v - $min) / $range) * ($H - 2 * $pad), 1),
            ];
        }

        $line = "M{$pts[0][0]},{$pts[0][1]}";
        for ($i = 1; $i < count($pts); $i++) {
            $mx   = round(($pts[$i - 1][0] + $pts[$i][0]) / 2, 1);
            $line .= " C{$mx},{$pts[$i-1][1]} {$mx},{$pts[$i][1]} {$pts[$i][0]},{$pts[$i][1]}";
        }
        $last = end($pts);
        $area = "{$line} L{$last[0]},{$H} L0,{$H} Z";

        $id = 'sg' . substr(md5(implode(',', $values) . $color), 0, 8);

        return <<<SVG
<svg viewBox="0 0 {$W} {$H}" width="100%" height="{$H}" preserveAspectRatio="none">
  <defs>
    <linearGradient id="{$id}" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="{$color}" stop-opacity="0.35"/>
      <stop offset="100%" stop-color="{$color}" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <path d="{$area}" fill="url(#{$id})"/>
  <path d="{$line}" stroke="{$color}" stroke-width="1.5" fill="none" stroke-linecap="round"/>
</svg>
SVG;
    }
}
