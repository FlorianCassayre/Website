<?php
/**
 * Created by IntelliJ IDEA.
 * User: Florian
 * Date: 25/11/2016
 * Time: 22:41
 */

namespace FlorianCassayre\Controllers\Tools;

use Silex\Application;
use FlorianCassayre\Api\Minecraft\MinecraftEnchanting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MCEnchantingController
{
    public function enchanting_home(Application $app, Request $request)
    {
        if ($request->query->has('type') && $request->query->has('material') && $request->query->has('levels'))
        {
            $type = $request->query->get('type');
            $material = $request->query->get('material');
            $levels = $request->query->get('levels');

            if($request->query->has('known_enchantment') && $request->query->has('known_enchantment_level'))
            {
                $enchantment = $request->query->get('known_enchantment');

                $enchantment_level = $request->query->get('known_enchantment_level');

                return $app->redirect($app['url_generator']->generate('tools.minecraft.enchanting.result.conditional', array(
                    'type' => $type,
                    'material' => $material,
                    'levels' => $levels,
                    'known_enchantment' => $enchantment,
                    'known_enchantment_level' => $enchantment_level
                )));
            }
            else
            {
                return $app->redirect($app['url_generator']->generate('tools.minecraft.enchanting.result', array(
                    'type' => $type,
                    'material' => $material,
                    'levels' => $levels
                )));
            }
        }

        return $app['twig']->render('tools/enchanting.html.twig', array(
            'is_empty' => true,
            'items_select' => MinecraftEnchanting::getItemSelect(),
            'enchantments_select' => array_values(MinecraftEnchanting::getEnchantments()),
            'quote' => $this->getRandomQuote()
        ));
    }

    public function enchanting(Application $app, Request $request, $type, $material, $levels)
    {
        return $this->enchanting_conditional($app, $request, $type, $material, $levels, null, 0);
    }

    public function enchanting_conditional(Application $app, Request $request, $type, $material, $levels, $known_enchantment, $known_enchantment_level)
    {
        $error = null;
        $item_id = -1;
        $levels = intval($levels);
        $combinations = array();
        $enchantments_data = array();
        $enchantments_count = array();

        $is_known = $known_enchantment != null;
        $known_id = -1;
        $known_enchantment_level_any = $known_enchantment_level === 'any';
        $known_enchantment_level = intval($known_enchantment_level);
        $known_obj = null;
        $total_frequency = 0.0;

        try
        {
            $item_id = MinecraftEnchanting::getItemId($type, $material);

            if($levels < 1 || $levels > 30)
                $error = 'invalid_levels';

            if($is_known)
            {
                try
                {
                    $known_id = MinecraftEnchanting::getEnchantmentIdByCode($known_enchantment);
                    $known_obj = MinecraftEnchanting::getEnchantments()[$known_id];

                    if(!$known_enchantment_level_any && ($known_enchantment_level < 1 || $known_enchantment_level > $known_obj->max_level))
                        $error = 'invalid_enchantment_level';
                }
                catch(\InvalidArgumentException $ex)
                {
                    $error = 'invalid_enchantment';
                }
            }
        }
        catch(\InvalidArgumentException $ex)
        {
            $error = 'invalid_item';
        }



        if($error == null)
        {
            $combinations = MinecraftEnchanting::getCombinations($app['pdo'], $item_id, $levels);
            $number_enchant = array();

            // Conditional probabilities
            if($is_known)
            {
                $total_frequency = 0;

                $new_combinations = array();
                foreach($combinations as $combination) {
                    foreach($combination->enchantments as $enchantment)
                    {
                        if($enchantment->id == $known_id && ($enchantment->level == $known_enchantment_level || $known_enchantment_level_any)) // TODO check level ANY
                        {
                            $total_frequency += $combination->frequency;
                            array_push($new_combinations, $combination);
                            break;
                        }
                    }
                }
                $combinations = $new_combinations;

                foreach($combinations as $combination) {
                    $combination->frequency = $combination->frequency / $total_frequency;
                }

                if(count($combinations) == 0)
                {
                    $error = 'invalid_combination';
                }
            }


            $enchantments = array();
            foreach($combinations as $combination) {
                $count = count($combination->enchantments);
                if(!isset($number_enchant[$count]))
                    $number_enchant[$count] = 0;
                $number_enchant[$count] += $combination->frequency;

                foreach($combination->enchantments as $enchantment) {
                    if(!isset($enchantments[$enchantment->id]))
                        $enchantments[$enchantment->id] = array();
                    if(!isset($enchantments[$enchantment->id][$enchantment->level]))
                        $enchantments[$enchantment->id][$enchantment->level] = $combination->frequency;
                    else
                        $enchantments[$enchantment->id][$enchantment->level] += $combination->frequency;
                }
            }

            ksort($number_enchant);
            foreach($number_enchant as $number => $frequency)
            {
                array_push($enchantments_count, (object) array('count' => $number, 'frequency' => $frequency));
            }

            foreach($enchantments as $id => $lvls)
            {
                $name = MinecraftEnchanting::getEnchantmentNameById($id);
                $enchantments_levels = array();

                ksort($enchantments[$id]);
                foreach($enchantments[$id] as $level => $frequency)
                {
                    array_push($enchantments_levels, (object) array('name' => MinecraftEnchanting::getCleanEnchantmentName($id, $level), 'frequency' => $frequency));
                }

                array_push($enchantments_data, (object) array('name' => $name, 'levels' => $enchantments_levels));
            }
        }

        $code = 200;
        if($error != null)
            $code = 404;


        return new Response($app['twig']->render('tools/enchanting.html.twig', array(
            'is_empty' => false,
            'items_select' => MinecraftEnchanting::getItemSelect(),
            'enchantments_select' => array_values(MinecraftEnchanting::getEnchantments()),
            'success' => $error == null,
            'error' => $error,
            'selected_type' => $type,
            'selected_material' => $material,
            'selected_levels' => $levels,
            'is_known_enchantment' => $is_known,
            'selected_known_enchantment' => $known_enchantment,
            'selected_known_enchantment_level' => ($known_enchantment_level_any ? 'any' : $known_enchantment_level),
            'total_frequency' => $total_frequency,
            'enchantments_data' => $enchantments_data,
            'enchantments_count' => $enchantments_count,
            'combinations' => $combinations,
            'quote' => self::getRandomQuote()
        )), $code);
    }

    private function getRandomQuote()
    {
        $quotes = array(
            'Pour un même matériau, les outils (pioche, pelle & hache) possèdent les mêmes probabilités d\'enchantements.',
            'Les enchantements Sharpness V et Efficiency V ne sont pas obtenables sur des outils en diamant.',
            'Les enchantements Mending et Frost Walker ne peuvent être obtenus que par le biais des villageois.',
            'Il est impossible de cumuler certains enchantements, comme Silk Touch avec Fortune ou tous les types de Protection entre eux.',
            'Il est possible d\'obtenir Sharpness V ou Efficiency V en fusionnant des enchantements grâce à l\'enclume.',
            'Un outil possédant l\'enchantement Unbreaking III a une durabilité 4 fois plus élevée.',
            'Le système d\'enchantement a été ajoutée en 1.0.0',
            'Dans l\'inventaire de la table d\'enchantement, on peut apercevoir des symboles qui seraient issus du "Standard Galactic Alphabet" ; leur rôle est toutefois purement décoratif.',
            'Il existe 27 enchantements différents dans le jeu.',
            'Avant la version 1.3, il était possible de dépenser jusqu\'à 50 niveaux pour enchanter un objet.',
            '15 bibliothèques sont nécessaires pour pouvoir enchanter au niveau 30.',
            'Dans le menu de la table d\'enchantement, il est possible de voir le premier enchantement que l\'objet contiendra.',
            'Une épée peut obtenir jusqu\'à 5 enchantements en utilisant une table d\'enchantement',
            'Tuer l\'Enderdragon vous rapportera beaucoup de niveaux.',
            'Des objets comme les briquets ou les cisailles ne peuvent pas être enchantés à partir d\'une table d\'enchantement, mais peuvent toutefois recevoir des enchantements grâce à une enclume.',
            'En créatif, la commande /enchant accompagnée des arguments nécessaires permet d\'ajouter des enchantements à un objet.',
            'L\'enchantement Thorns III n\'est pas obtenable avec une table d\'enchantement.'
        );

        return $quotes[rand(0, count($quotes) - 1)];
    }
}