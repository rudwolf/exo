<?php
require __DIR__ . '/vendor/autoload.php';

use Exads\ABTestData as ABTestData;

class ExoAbTest {
    /**
     * Gets a random design based on the provided promoId
     *
     * @param integer $promoId id of the promotion
     * @return string Design name based on the percentage provided in a random way to the current user
     */
    public function get_random_design(int $promoId)
    {
        $designData = $this->getData($promoId);
        $rand = mt_rand(1, (int) array_sum($designData));

        foreach ($designData as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
    }

    /**
     * Gets the A/B Testing data
     *
     * @param integer $promoId id of the promotion
     * @return array Test data ready to be used in the random design function, containing the design and desired percentage
     */
    private function getData(int $promoId): array
    {
        $abTest = new ABTestData($promoId);
        $designs = $abTest->getAllDesigns();

        $design_data = [];
        foreach ($designs as $key => $design) {
            $design_data[$design['designName']] = $design['splitPercent'];
        }
        return $design_data;
    }

}

header('Content-Type: application/json;charset=utf-8');
$exo_ab_test = new ExoAbTest();
echo json_encode([
    'Promotion 1' => $exo_ab_test->get_random_design(1),
    'Promotion 2' => $exo_ab_test->get_random_design(2),
    'Promotion 3' => $exo_ab_test->get_random_design(3),
]);