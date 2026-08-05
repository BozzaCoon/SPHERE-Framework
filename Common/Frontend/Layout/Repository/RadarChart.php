<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class RadarChart
 *
 * Erzeugt ein Netz-/Radardiagramm als reinen SVG-String.
 *
 * Bewusst ohne CSS-Klassen, ohne <style> und ohne transform-Attribute gehalten,
 * damit die Ausgabe sowohl im Browser als auch in DomPdf (php-svg-lib) identisch
 * rendert und so ohne zweiten Renderpfad im Zeugnisdruck verwendet werden kann.
 *
 * Anzahl der Achsen ergibt sich aus count($LabelList); Beschriftung, Werte und
 * Maximalwert werden vollständig als Parameter übergeben.
 * Minimum 3 Achsen!
 *
 * @package SPHERE\Common\Frontend\Chart\Repository
 */
class RadarChart extends Extension implements ITemplateInterface
{

    /** @var string[] $LabelList Achsen-Beschriftungen (Zeilenumbruch \n wird unterstützt) */
    private array $LabelList;
    /** @var float[] $ValueList Achsen-Werte, gleiche Reihenfolge wie $LabelList */
    private array $ValueList;
    /** @var float $Max Höchstwert der Skala (Radius außen) */
    private float $Max;
    /** @var int $RingCount Anzahl der Gitterringe (= Skalen-Schritte) */
    private int $RingCount;
    /** @var int $Width Breite der SVG-Zeichenfläche */
    private int $Width;
    /** @var int $Height Höhe der SVG-Zeichenfläche */
    private int $Height;
    /** @var int $RadiusSpace Höhe, die die Beschreibung Platz hat. Interessant bei Mehrzeilern oder anderer Schriftgröße */
    private int $RadiusSpace = 35;
    /** @var string $Color Linien-/Füllfarbe des Werte-Polygons */
    private string $Color = '#3987e5';
    /** @var bool $IsInverted Skala invertieren (Noten: kleiner Wert = größter Ausschlag) */
    private bool $IsInverted = false;
    /** @var int $FontSize Schriftgröße skaliert mit der Ausgabe mit */
    private int $FontSize = 11;
    /** @var int $ScaleSize Schriftgröße Skala (wird bei Schriftgröße mit gesetzt (-1)*/
    private int $ScaleSize = 10;

    /**
     * @param string[]   $LabelList  Achsen-Beschriftungen
     * @param float[]    $ValueList  Achsen-Werte (gleiche Reihenfolge/Anzahl wie $LabelList)
     * @param float|null $Max        Höchstwert der Skala; null = automatisch aus dem größten Wert
     * @param int        $Width      SVG-Breite
     * @param int        $Height     SVG-Höhe
     */
    public function __construct(
        array $LabelList,
        array $ValueList,
        $Max = null,
        $Width = 470,
        $Height = 300
    ) {

        $this->LabelList = array_values($LabelList);
        $this->ValueList = array_values($ValueList);

        if ($Max === null) {
            $MaxValue = empty($this->ValueList) ? 1 : max($this->ValueList);
            $Max = $MaxValue > 0 ? ceil($MaxValue * 2) / 2 : 1;
        }
        $this->Max = (float)($Max > 0 ? $Max : 1);
        $this->RingCount = (int)$this->Max;
        $this->Width = (int)$Width;
        $this->Height = (int)$Height;
    }

    // SVG als data-URI-Bild einbetten; DomPdf rendert dies über php-svg-lib
    // zuverlässiger als ein inline-<svg>-Element.
    /**
     * @return string data-URI-Bild
     */
    public function getImage()
    {
        return '<img src="data:image/svg+xml;base64,' . base64_encode($this->getContent()) . '"'
            . ' style="width:'.$this->Width.'px; height:'.$this->Height.'px;"/>';
    }

    /**
     * @return $this
     */
    public function setInverted(): RadarChart
    {

        $this->RingCount--;
        $this->IsInverted = true;
        return $this;
    }

    /**
     * @param int $FontSize
     * @param null|int $ScaleSize
     * @return $this
     */
    public function setFontSize(int $FontSize, ?int $ScaleSize = null): RadarChart
    {

        $this->FontSize = $FontSize;
        if($ScaleSize){
            $this->ScaleSize = $ScaleSize;
        } else {
            $this->ScaleSize = $FontSize - 1;
        }

        return $this;
    }

    /**
     * @param string $Color
     * @return $this
     */
    public function setColor(string $Color = '#000'): RadarChart
    {

        $this->Color = $Color;
        return $this;
    }

    /**
     * @param int $RingCount
     * @return $this
     */
    public function setRingCount(int $RingCount = 6): RadarChart
    {

        $this->RingCount = $RingCount;
        return $this;
    }

    /**
     * @param int $RadiusSpace
     * @return $this
     */
    public function setRadiusSpace(int $RadiusSpace = 35): RadarChart
    {

        $this->RadiusSpace = $RadiusSpace;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $Count = count($this->LabelList);
        if ($Count < 3) {
            // Ein Netzdiagramm benötigt mindestens 3 Achsen
            return '';
        }

        $CenterX = $this->Width / 2;
        $CenterY = $this->Height / 2;
        // Radius mit Rand für die Beschriftungen
        $Radius = (int)(min($this->Width, $this->Height) / 2) - $this->RadiusSpace; //- 35; // -65 Rand

        $Svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $this->Width . '" height="' . $this->Height . '"'
            . ' viewBox="0 0 ' . $this->Width . ' ' . $this->Height . '">';

        // Gitterringe
        for ($Ring = 1; $Ring <= $this->RingCount; $Ring++) {
            $RingRadius = $Radius * $Ring / $this->RingCount;
            $Svg .= '<path d="' . $this->getPolygonPath($Count, $CenterX, $CenterY, $RingRadius)
                . '" fill="none" stroke="#d3d1c7" stroke-width="1"/>';
        }

        // Achsenlinien und Beschriftungen
        for ($Index = 0; $Index < $Count; $Index++) {
            $Point = $this->getPoint($Index, $Count, $CenterX, $CenterY, $Radius);
            $Svg .= '<line x1="' . $this->format($CenterX) . '" y1="' . $this->format($CenterY) . '"'
                . ' x2="' . $this->format($Point[0]) . '" y2="' . $this->format($Point[1]) . '"'
                . ' stroke="#d3d1c7" stroke-width="1"/>';
            $Svg .= $this->getLabel($Index, $Count, $CenterX, $CenterY, $Radius);
        }

        // Werte-Polygon
        $DataPath = '';
        for ($Index = 0; $Index < $Count; $Index++) {
            $Value = isset($this->ValueList[$Index]) ? (float)$this->ValueList[$Index] : 0;
            $ValueRadius = $this->getValueRadius($Value, $Radius);
            $Point = $this->getPoint($Index, $Count, $CenterX, $CenterY, $ValueRadius);
            $DataPath .= ($Index === 0 ? 'M' : ' L') . $this->format($Point[0]) . ' ' . $this->format($Point[1]);
        }
        $DataPath .= ' Z';
        $Svg .= '<path d="' . $DataPath . '" fill="' . $this->Color . '" fill-opacity="0.12"'
            . ' stroke="' . $this->Color . '" stroke-width="3" stroke-linejoin="round"/>';


        // Skalen-Beschriftung (entlang der ersten Achse nach oben)
        list($ScaleInner, $ScaleOuter) = $this->getScaleBounds();
        for ($Ring = 0; $Ring <= $this->RingCount; $Ring++) {
            $Fraction = $Ring / $this->RingCount;
            $Value = $ScaleInner + ($ScaleOuter - $ScaleInner) * $Fraction;
            $LabelY = $CenterY - $Radius * $Fraction + 3;
            $Svg .= '<text x="' . $this->format($CenterX + 3) . '" y="' . $this->format($LabelY) . '"'
                . ' font-size="'.$this->ScaleSize.'" fill="#555555">' . $this->getGermanNumber($Value) . '</text>'; // fill="#a8a69c"
        }

        // Werte-Punkte
        for ($Index = 0; $Index < $Count; $Index++) {
            $Value = isset($this->ValueList[$Index]) ? (float)$this->ValueList[$Index] : 0;
            $ValueRadius = $this->getValueRadius($Value, $Radius);
            $Point = $this->getPoint($Index, $Count, $CenterX, $CenterY, $ValueRadius);
            $Svg .= '<circle cx="' . $this->format($Point[0]) . '" cy="' . $this->format($Point[1]) . '"'
                . ' r="3.5" fill="' . $this->Color . '"/>';
        }

        $Svg .= '</svg>';

        return $Svg;
    }

    /**
     * @return string
     */
    public function getWidth()
    {
        return $this->Width;
    }

    /**
     * @return string
     */
    public function getHeight()
    {
        return $this->Height;
    }

    /**
     * Berechnet die Koordinaten der Achse $Index (Start oben, im Uhrzeigersinn).
     *
     * @param int   $Index
     * @param int   $Count
     * @param float $CenterX
     * @param float $CenterY
     * @param float $Radius
     *
     * @return float[] [x, y]
     */
    private function getPoint($Index, $Count, $CenterX, $CenterY, $Radius)
    {

        $Angle = -M_PI / 2 + $Index * 2 * M_PI / $Count;
        return array(
            $CenterX + $Radius * cos($Angle),
            $CenterY + $Radius * sin($Angle)
        );
    }

    /**
     * Skalen-Grenzen [Zentrum, Rand].
     * Punkte:  0 im Zentrum, Max außen (großer Wert = großer Ausschlag).
     * Noten:   Max (schlechteste Note) im Zentrum, 1 (beste Note) außen.
     *
     * @return float[] [innen, außen]
     */
    private function getScaleBounds()
    {

        if ($this->IsInverted) {
            return array($this->Max, 1.0);
        }
        return array(0.0, $this->Max);
    }

    /**
     * Radius eines Wertes anhand der Skalen-Grenzen (0..$Radius), begrenzt auf den Rand.
     *
     * @param float $Value
     * @param float $Radius
     *
     * @return float
     */
    private function getValueRadius($Value, $Radius)
    {

        list($Inner, $Outer) = $this->getScaleBounds();
        if ($Outer == $Inner) {
            return 0;
        }
        $Fraction = ((float)$Value - $Inner) / ($Outer - $Inner);
        if ($Fraction < 0) {
            $Fraction = 0;
        }
        if ($Fraction > 1) {
            $Fraction = 1;
        }
        return $Radius * $Fraction;
    }

    /**
     * Erzeugt den Pfad (d-Attribut) eines geschlossenen Vielecks für einen Ring.
     *
     * @param int   $Count
     * @param float $CenterX
     * @param float $CenterY
     * @param float $Radius
     *
     * @return string
     */
    private function getPolygonPath($Count, $CenterX, $CenterY, $Radius)
    {

        $Path = '';
        for ($Index = 0; $Index < $Count; $Index++) {
            $Point = $this->getPoint($Index, $Count, $CenterX, $CenterY, $Radius);
            $Path .= ($Index === 0 ? 'M' : ' L') . $this->format($Point[0]) . ' ' . $this->format($Point[1]);
        }
        return $Path . ' Z';
    }

    /**
     * Erzeugt die Achsen-Beschriftung (mehrzeilig über \n möglich).
     *
     * @param int   $Index
     * @param int   $Count
     * @param float $CenterX
     * @param float $CenterY
     * @param float $Radius
     *
     * @return string
     */
    private function getLabel($Index, $Count, $CenterX, $CenterY, $Radius)
    {

        $Point = $this->getPoint($Index, $Count, $CenterX, $CenterY, $Radius + 18);
        $PositionX = $Point[0];
        $PositionY = $Point[1];

        if (abs($PositionX - $CenterX) < 8) {
            $Anchor = 'middle';
        } elseif ($PositionX < $CenterX) {
            $Anchor = 'end';
        } else {
            $Anchor = 'start';
        }

        $Lines = explode("\n", $this->LabelList[$Index]);
        $LineCount = count($Lines);
        $Svg = '';
        foreach ($Lines as $LineIndex => $Line) {
            $LineY = $PositionY + $LineIndex * $this->FontSize - ($LineCount - 1) * 5;
            $Svg .= '<text x="' . $this->format($PositionX) . '" y="' . $this->format($LineY) . '"'
                . ' font-size="'.$this->FontSize.'" fill="#52514e" text-anchor="' . $Anchor . '">'
                . $this->escape($Line) . '</text>';
        }
        return $Svg;
    }

    /**
     * Formatiert eine Koordinate für die SVG-Ausgabe (Punkt als Dezimaltrenner).
     *
     * @param float $Number
     *
     * @return string
     */
    private function format($Number)
    {

        return number_format((float)$Number, 1, '.', '');
    }

    /**
     * Formatiert einen Skalenwert in deutscher Schreibweise (Komma).
     *
     * @param float $Number
     *
     * @return string
     */
    private function getGermanNumber($Number)
    {

        $Formatted = number_format((float)$Number, 1, ',', '');
        // Ganze Zahlen ohne Nachkommastelle darstellen
        return preg_replace('/,0$/', '', $Formatted);
    }

    /**
     * XML-Escaping für Beschriftungstexte.
     *
     * @param string $Text
     *
     * @return string
     */
    private function escape($Text)
    {

        return htmlspecialchars($Text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
