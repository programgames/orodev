<?php

namespace Programgames\OroDev\Requirements\Application;

interface OroApplicationRequirementsInterface
{
    public function getMandatoryRequirements();
    public function getPhpIniRequirements();
    public function getOroRequirements();
    public function getRecommendations();
}
