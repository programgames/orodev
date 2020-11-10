<?php

namespace Programgames\OroDev\Requirements;

interface OroApplicationRequirementsInterface
{
    public function getMandatoryRequirements();
    public function getPhpIniRequirements();
    public function getOroRequirements();
    public function getRecommendations();
}
