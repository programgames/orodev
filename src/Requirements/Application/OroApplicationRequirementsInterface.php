<?php

namespace Programgames\OroDev\Requirements\Application;

interface OroApplicationRequirementsInterface
{
    public function getMandatoryRequirements();
    public function getPhpConfigRequirements();
    public function getOroRequirements();
    public function getRecommendations();
}
